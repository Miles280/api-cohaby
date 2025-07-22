<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Adress;
use App\Enum\Gender;
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
        // Création d'une adresse fictive
        $adress = new Adress();
        $adress->setStreet('123 Main Street');
        $adress->setCity('Lyon');
        $adress->setPostalCode('69000');
        $adress->setRegion('Auvergne-Rhône-Alpes');
        $adress->setCountry('France');
        $adress->setLatitude(45.75);
        $adress->setLongitude(4.85);

        $manager->persist($adress);

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setPseudo('johndoe');
        $user->setBirthdate(new \DateTimeImmutable('1990-01-01'));
        $user->setInscriptionDate(new \DateTimeImmutable('now'));
        $user->setGender(Gender::MALE);
        $user->setRoles(['ROLE_USER']);

        // Association de l'adresse
        $user->setAdress($adress);

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'mdp');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        $manager->flush();
    }
}
