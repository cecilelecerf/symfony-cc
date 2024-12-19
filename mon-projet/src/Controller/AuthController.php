<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\RegistrationFormType;
use App\Enum\UserAccountStatusEnum;
use App\Form\ResetForm;
use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('auth/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgot', name: 'forgot')]
    public function forgot(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        Environment $twig,

    ): Response {
        $email = $request->get('_email');
        if ($email) {
            $user = $userRepository->findOneBy(['email' => $email]);
            $resetPasswordToken = Uuid::v4()->toRfc4122();
            $user->setResetPasswordToken($resetPasswordToken);
            $entityManager->flush();
            $email = (new Email())
                ->from('contact@streemi.fr')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié')
                ->html($twig->render('emails/forgot.html.twig', ['user' => $user]));

            $mailer->send($email);

            $this->addFlash('success', "Un email a été envoyé si l'email existe ");
        } else {
            $this->addFlash('error', "Un email n'sa été envoyé si l'email existe ");
        }
        return $this->render("/auth/forgot.html.twig");
    }
    #[Route(path: '/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get("password")->getData()
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(["ROLE_USER"]);
            $user->setAccountStatus(UserAccountStatusEnum::ACTIVE);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render("/auth/register.html.twig",  [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/reset/{uid}', name: 'reset')]
    public function reset(
        string $uid,
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(["resetPasswordToken" => $uid]);
        if (!$user) {
            $this->addFlash('error', 'Token de réinitialisation invalide');
            return $this->redirectToRoute('forgot');
        }
        $form = $this->createForm(ResetForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get("password")->getData()
            );
            $user->setPassword($hashedPassword);
            $user->setResetPasswordToken(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->redirectToRoute('app_login');
        }
        return $this->render("/auth/reset.html.twig", [
            'resetForm' => $form->createView(),
        ]);
    }
}
