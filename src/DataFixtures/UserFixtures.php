<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    /**
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        //Init Faker
        $faker = Factory::create('fr_FR');
        for($i = 0; $i < 5; $i++) {
            $u = new User();
            $u->setEmail($faker->email());
            $u->setPassword($this->hasher->hashPassword($u, "toto"));
            $u->setRoles([
                "ROLE_USER"
            ]);
            $manager->persist($u);
        }
        $u = new User();
        $u->setEmail("esteban@fpluriel.org");
        $u->setPassword($this->hasher->hashPassword($u, "oui"));
        $u->setRoles([
            "ROLE_ADMIN"
        ]);
        $manager->persist($u);
        $manager->flush();
    }
}
