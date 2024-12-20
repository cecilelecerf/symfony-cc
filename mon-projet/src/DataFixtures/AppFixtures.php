<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Reaction;
use App\Entity\Tag;
use App\Enum\ReactionTypeEnum;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->generateUsers($manager);
        $tags = $this->generateTags($manager);
        $publications = $this->generatePublications($manager, $users, $tags);
        $this->generateComments($manager, $users, $publications);
        $this->generateReactions($manager, $users, $publications);
        $manager->flush();

        // for user test
        $user = $this->generateUser($manager);
        $this->generateAdmin($manager);

        $manager->flush();
    }

    private function generateUsers(ObjectManager $manager): array
    {
        $faker = Factory::create();

        $users = [];

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $faker->password()
            );
            $user->setUsername($faker->firstname());
            $user->setEmail($faker->email());
            $user->setPassword($hashedPassword);
            // $user->setAccountStatus(UserAccountStatusEnum::ACTIVE);
            $user->setRoles(["ROLE_USER"]);
            $users[] = $user;
            $manager->persist($user);
        }
        return $users;
    }
    protected function generateTags(ObjectManager $manager): array
    {
        $faker = Factory::create();

        $tags = [];
        for ($i = 0; $i < 5; $i++) {
            $tag = new Tag();
            $tag->setLabel($faker->sentence(1));

            $tags[] = $tag;
            $manager->persist($tag);
        }
        return $tags;
    }
    private function generatePublications(ObjectManager $manager, array $users, array $tags): array
    {
        $faker = Factory::create();

        foreach ($users as $user) {
            // Créer des posts
            $publications = [];
            for ($i = 0; $i < 5; $i++) {
                $publication = new Publication();
                $publication->setTitle($faker->sentence(3))
                    ->setContent($faker->paragraph(2))
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setModifiedAt(new DateTimeImmutable())
                    ->setUser($user);
                for ($i = 0; $i < random_int(0, count($tags) - 1); $i++) {
                    $publication->addTag($tags[random_int(0, count($tags) - 1)]);
                }
                $publications[] = $publication;
                $manager->persist($publication);
            }
        }
        return $publications;
    }

    private function generateComments(ObjectManager $manager, array $users, array $publications): array
    {
        $faker = Factory::create();

        foreach ($publications as $publication) {
            $comments = [];
            for ($i = 0; $i < random_int(1, 3); $i++) {
                $comment = new Comment();
                $comment->setContent($faker->paragraph(1))
                    ->setCreatedAt(new DateTimeImmutable())

                    ->setModifiedAt(new DateTimeImmutable())
                    ->setUser($users[random_int(0, count($users) - 1)])
                    ->setPublication($publication);

                $manager->persist($comment);
                $comments[] = $comment;
            }
        }
        return $comments;
    }
    private function generateReactions(ObjectManager $manager, array $users, array $publications): void
    {
        foreach ($publications as $publication) {
            for ($i = 0; $i < random_int(0, 5); $i++) {
                $reaction = new Reaction();
                $reaction->setType([ReactionTypeEnum::LIKE, ReactionTypeEnum::DISLIKE][array_rand([ReactionTypeEnum::LIKE, ReactionTypeEnum::DISLIKE])])
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setModifiedAt(new DateTimeImmutable())
                    ->setUser($users[random_int(0, count($users) - 1)])
                    ->setPublication($publication);
                $manager->persist($reaction);
            }
        }
    }



    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // --------------------------------USER-----------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------

    private function generateUser(ObjectManager $manager): User
    {
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            "user"
        );
        $user->setUsername("user");
        $user->setEmail("user@gmail.com");
        $user->setPassword($hashedPassword);
        $user->setRoles(["ROLE_USER"]);

        $manager->persist($user);

        return $user;
    }

    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // --------------------------------ADMIN-----------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------
    // -----------------------------------------------------------------------

    private function generateAdmin(ObjectManager $manager): void
    {
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            "admin"
        );
        $user->setUsername("admin");
        $user->setEmail("admin@gmail.com");
        $user->setPassword($hashedPassword);
        $user->setRoles(["ROLE_ADMIN"]);

        $manager->persist($user);
    }
}
