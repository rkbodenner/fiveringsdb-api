<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Alsciende <alsciende@icloud.com>
 */
class UserManager
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct (EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function findUserByUsername (string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function createUser (string $username): User
    {
        $user = new User();
        $user->setUsername($username)
             ->setPassword($username)
             ->setEnabled(true);

        return $user;
    }

    public function updateUser (User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
