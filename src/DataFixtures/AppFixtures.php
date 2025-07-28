<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Adress;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Equipment;
use App\Entity\Listing;
use App\Entity\Message;
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
        
        
        // ----- Réservations + Commentaires -----
        $bookingsCount = 80;

        $sampleComments = [
            "Super séjour, je recommande !",
            "Hôte très sympathique et logement propre.",
            "Bonne expérience dans l’ensemble.",
            "Quelques soucis avec l’eau chaude.",
            "Très bon rapport qualité/prix.",
            "Emplacement parfait, je reviendrai.",
            "Un peu bruyant la nuit.",
            "Propre et bien équipé, conforme aux photos.",
            "Accueil chaleureux, logement agréable.",
            "Déçu par la propreté."
        ];

        for ($i = 0; $i < $bookingsCount; $i++) {
            $booking = new Booking();

            $listing = $listingRefs[array_rand($listingRefs)];

            // Booker ≠ Owner
            do {
                $user = $userRefs[array_rand($userRefs)];
            } while ($user === $listing->getOwner());

            $beginningDate = new \DateTimeImmutable(sprintf('+%d days', random_int(1, 90)));
            $nights = random_int(2, 14);
            $endingDate = $beginningDate->modify("+$nights days");

            $booking->setUser($user);
            $booking->setListing($listing);
            $booking->setBeginningDate(\DateTime::createFromImmutable($beginningDate));
            $booking->setTotalNights($nights);
            $booking->setStatus(\App\Enum\BookingStatus::ACCEPTED);
            $booking->setNbrGuests(random_int(1, $listing->getMaxCapacity()));
            $booking->setTotalPrice($listing->getPricePerNight() * $nights);

            $manager->persist($booking);

            // 1 chance sur 2 d'ajouter un commentaire
            if (random_int(0, 1)) {
                $comment = new Comment();
                $comment->setBooking($booking);
                $comment->setAuthor($user);
                $comment->setRating(random_int(1, 5));
                $comment->setContent($sampleComments[array_rand($sampleComments)]);
                $comment->setSentAt((new \DateTime())->modify(sprintf('-%d days', random_int(0, 30)))); // un commentaire récent

                $manager->persist($comment);
            }
        }


        // ----- Messages -----
        $messagesData = json_decode(file_get_contents(__DIR__ . '/fixtures_messages.json'), true);

        foreach ($messagesData as $data) {
            $message = new Message();

            $message->setContent($data['content']);
            $message->setSendAt(new \DateTime($data['sendAt']));
            $message->setIsRead($data['isRead']);
            $message->setSender($userRefs[$data['sender']]);
            $message->setReceiver($userRefs[$data['receiver']]);

            $manager->persist($message);
        }


        $manager->flush();
    }
}
