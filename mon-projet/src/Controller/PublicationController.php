<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Reaction;
use App\Enum\ReactionTypeEnum;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PublicationRepository;
use App\Repository\ReactionRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/publications', name: 'publications_')]
class PublicationController extends AbstractController
{
    #[Route(path: '/', name: 'all_publications')]
    public function allPublications(
        PublicationRepository $publicationRepository,
        Request $request,
        TagRepository $tagRepository
    ): Response {
        // Récupérer l'ID du tag depuis la requête
        $tagId = $request->query->get('tag');
        // Récupérer tous les tags disponibles
        $tags = $tagRepository->findAll();

        // Si un tag est sélectionné, on filtre les publications par ce tag
        if ($tagId) {
            $tag = $tagRepository->find($tagId);
            if ($tag) {
                $query = $publicationRepository->createQueryBuilder('p')
                    ->join('p.tag', 't')  // Jointure avec la table Tag
                    ->where('t.id = :tagId')
                    ->setParameter('tagId', $tag->getId())  // Utilisation de l'ID du tag
                    ->orderBy('p.createdAt', 'DESC')
                    ->getQuery();
                // $publications = $publicationRepository->findByTag($tag);
            } else {
                // Si le tag n'existe pas, retourner une réponse avec un message d'erreur
                $this->addFlash('error', 'Tag non trouvé');
                $query = $publicationRepository->createQueryBuilder('p')
                    ->orderBy('p.createdAt', 'DESC')
                    ->getQuery();
            }
        } else {
            // Si aucun tag n'est sélectionné, afficher toutes les publications
            $query = $publicationRepository->createQueryBuilder('p')
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery();
        }

        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // Créer la requête pour trier les publications par date de création


        // Appliquer la pagination
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // Calculer le nombre total de pages
        $totalPages = ceil(count($paginator) / $limit);

        return $this->render("publications/allPublications.html.twig", [
            'publications' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'tags' => $tags
        ]);
    }


    #[Route(path: '/{id}', name: 'single_publication')]
    public function singlePublication(
        PublicationRepository $publicationRepository,
        ReactionRepository $reactionRepository,
        CommentRepository $commentRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        int $id
    ): Response {
        // Récupérer la publication
        $publication = $publicationRepository->find($id);
        if (!$publication) {
            throw $this->createNotFoundException('Publication non trouvée');
        }

        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour interagir.');
            return $this->redirectToRoute('app_login');
        }

        // Compter les likes/dislikes
        $likeCount = $reactionRepository->countReactionsForPublication($publication, ReactionTypeEnum::LIKE);
        $dislikeCount = $reactionRepository->countReactionsForPublication($publication, ReactionTypeEnum::DISLIKE);

        // Vérifier si l'utilisateur a déjà réagi
        $userReaction = $reactionRepository->findOneBy(['publication' => $publication, 'user' => $user]);

        // Gestion des likes/dislikes via POST
        if ($request->isMethod('POST') && $request->request->get('type')) {
            $reactionType = $request->request->get('type') === "LIKE" ? ReactionTypeEnum::LIKE : ReactionTypeEnum::DISLIKE;
            if ($userReaction) {
                // Mise à jour de la réaction existante
                if ($userReaction->getType() === $reactionType) {
                    $entityManager->remove($userReaction); // Supprimer la réaction si elle est identique
                } else {
                    $userReaction->setType($reactionType);
                    $userReaction->setModifiedAt(new \DateTimeImmutable());
                    $entityManager->persist($userReaction);
                }
            } else {
                // Nouvelle réaction
                $reaction = new Reaction();
                $reaction->setPublication($publication);
                $reaction->setUser($user);
                $reaction->setType($reactionType);
                $reaction->setCreatedAt(new \DateTimeImmutable());
                $reaction->setModifiedAt(new \DateTimeImmutable());
                $entityManager->persist($reaction);
            }

            $entityManager->flush();
            return $this->redirectToRoute('publications_single_publication', ['id' => $id]);
        }

        // Récupérer les commentaires
        $comments = $commentRepository->findBy(['publication' => $publication], ['createdAt' => 'ASC']);

        // Formulaire pour ajouter un commentaire
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setPublication($publication);
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setModifiedAt(new \DateTimeImmutable());
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté.');
            return $this->redirectToRoute('publications_single_publication', ['id' => $id]);
        }
        // var_dump($userReaction->getType() == ReactionTypeEnum::LIKE);
        return $this->render("publications/singlePublication.html.twig", [
            'publication' => $publication,
            'comments' => $comments,
            'likeCount' => $likeCount,
            'dislikeCount' => $dislikeCount,
            'isUserReaction' => $userReaction,
            'commentForm' => $commentForm->createView(),
            'reactionTypeLike' => ReactionTypeEnum::LIKE,
            'reactionTypeDislike' => ReactionTypeEnum::DISLIKE,
        ]);
    }
}
