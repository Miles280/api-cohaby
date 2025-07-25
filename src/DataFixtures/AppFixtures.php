<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Adress;
use App\Entity\Equipment;
use App\Entity\Listing;
use App\Entity\Picture;
use App\Entity\Service;
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
        // ----- ADRESSES -----
        $adressData = json_decode(file_get_contents(__DIR__ . '/fixtures_adresses.json'), true);
        $adressRefs = [];
        
        foreach ($adressData as $index => $data) {
            $adress = new Adress();
            $adress->setStreet($data['street']);
            $adress->setCity($data['city']);
            $adress->setPostalCode($data['postalCode']);
            $adress->setRegion($data['region']);
            $adress->setCountry($data['country']);
            $adress->setLatitude($data['latitude']);
            $adress->setLongitude($data['longitude']);

            $manager->persist($adress);

            $adressRefs[$index + 1] = $adress;
        }


        // ----- Utilisateurs -----
        $userData = json_decode(file_get_contents(__DIR__ . '/fixtures_users.json'), true);
        $userRefs = [];

        foreach ($userData as $index => $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setPseudo($data['pseudo']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setGender(Gender::from($data['gender']));
            $user->setBirthdate(new \DateTimeImmutable($data['birthdate']));
            $user->setInscriptionDate(new \DateTimeImmutable($data['inscriptionDate']));
            $user->setProfilPicture($data['profilPicture']);
            $user->setRoles($data['roles']);

            $adressId = random_int(1, count($adressRefs));
            $user->setAdress($adressRefs[$adressId]);

            $manager->persist($user);

            $userRefs[$index + 1] = $user;
        }
        

        // ----- Services -----
        $serviceData = json_decode(file_get_contents(__DIR__ . '/fixtures_services.json'), true);
        $serviceRefs = [];

        foreach ($serviceData as $index => $data) {
            $service = new Service();
            $service->setName($data['name']);
            $service->setdescription($data['description']);
            $service->seticon($data['icon']);

            $manager->persist($service);

            $serviceRefs[$index + 1] = $service;
        }


        // ----- Equipements -----
        $equipmentData = json_decode(file_get_contents(__DIR__ . '/fixtures_equipments.json'), true);
        $equipmentRefs = [];

        foreach ($equipmentData as $index => $data) {
            $equipment = new Equipment();
            $equipment->setName($data['name']);
            $equipment->setDescription($data['description']);
            $equipment->seticon($data['icon']);

            $manager->persist($equipment);

            $equipmentRefs[$index + 1] = $equipment;
        }


        // ----- Annonces -----
        $pictureData = json_decode(file_get_contents(__DIR__ . '/fixtures_pictures.json'), true);
        $listingData = json_decode(file_get_contents(__DIR__ . '/fixtures_listings.json'), true);
        $listingRefs = [];

        foreach ($listingData as $index => $data) {
            $listing = new Listing();
            $listing->setTitle($data['title']);
            $listing->setDescription($data['description']);
            $listing->setPricePerNight($data['pricePerNight']);
            $listing->setMaxCapacity($data['maxCapacity']);

            $adressId = random_int(1, count($adressRefs));
            $listing->setAdress($adressRefs[$adressId]);

            $ownerId = random_int(1, count($userRefs));
            $listing->setOwner($userRefs[$ownerId]);

            $nbServices = random_int(4, 8);
            $randomServices = array_rand($serviceRefs, $nbServices);

            foreach ((array) $randomServices as $key) {
                $listing->addService($serviceRefs[$key]); 
            }

            $nbEquipments = random_int(4, 8);
            $randomEquipments = array_rand($equipmentRefs, $nbEquipments);

            foreach ((array) $randomEquipments as $key) {
                $listing->addEquipment($equipmentRefs[$key]); 
            }

            $nbPictures = random_int(4, 10);
            $randomPictureKeys = array_rand($pictureData, $nbPictures);
            foreach ((array) $randomPictureKeys as $picKey) {
                $picture = new Picture();
                $picture->setUrl($pictureData[$picKey]['url']);
                $picture->setListing($listing);
                $listing->addPicture($picture);
                $manager->persist($picture);
            }

            $manager->persist($listing);

            $listingRefs[$index + 1] = $listing;
        }


        $manager->flush();
    }
}
