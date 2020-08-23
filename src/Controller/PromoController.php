<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Promo;
use App\Entity\Groupe;
use App\Entity\Apprenant;
use App\Repository\PromoRepository;
use App\Repository\ProfilRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class PromoController extends AbstractController
{
   

    /**
     * @Route(
     *     path="/api/promos",
     *     name="promo_add",
     *     methods={"POST"},
     *     defaults={
     *          "__controller"="App\Controller\PromoController::addPromo",
     *          "__api_resource_class"=Promo::class,
     *          "__api_collection_operation_name"="add_promo"
     *     }
     * )
     */
    public function addPromo(Request $request, UserPasswordEncoderInterface $encoder, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $manager,\Swift_Mailer $mailer, ProfilRepository $repoProfil)
    {
        $promo=new Promo();
        $promo->setLibelle("Sonatel Academy")
            ->setArchive(0)
        ;
        $groupe=new Groupe();
        $groupe->setArchive(0)
            ->setLibelle("principal")
            ->setDateCreation(new \DateTime)
        ;

        $tab=["diopp1017@gmail.com"];
        for($i=0;$i<1;$i++) {
            $apprenant = new Apprenant();
            $apprenant->setGroupe($groupe)
                ->setStatut("actif");
            $utilisateur = new User();
            $password = "pass_1234";
            $utilisateur->setUsername("hihh".$i)
                ->setNom("FGGH".$i)
                ->setPrenom("hdehfhf".$i)
                ->setTelephone("786545669")
                ->setGenre("F")
                ->setEmail($tab[$i])
                ->setPassword($encoder->encodePassword($utilisateur, $password))
                ->setArchives(0);
        }

        /*$doc = $request->files->get("document");
        $file= IOFactory::identify($doc);
        $reader= IOFactory::createReader($file);
        $spreadsheet=$reader->load($doc);
        $array_contenu_fichier= $spreadsheet->getActivesheet()->toArray();
        //dd($array_contenu_fichier);
        $password="pass_1234";
        for ($i=1;$i<count($array_contenu_fichier);$i++){
            $apprenant = new Apprenant();
            $apprenant->setGroupe($groupe)
                ->setStatut("actif");
            $utilisateur=new User();
            $utilisateur->setLogin($array_contenu_fichier[$i][0])
                ->setPassword($encoder->encodePassword($utilisateur,$password))
                ->setNom($array_contenu_fichier[$i][1])
                ->setPrenom($array_contenu_fichier[$i][2])
                ->setTelephone($array_contenu_fichier[$i][3])
                ->setEmail($array_contenu_fichier[$i][4])
                ->setGenre($array_contenu_fichier[$i][5])
                ->setAdresse($array_contenu_fichier[$i][6])
                ->setArchives(0);
        }*/

        $utilisateur->setProfil($repoProfil->findOneByLibelle("APPRENANT"));
        $apprenant->setUser($utilisateur);
        $groupe->addApprenant($apprenant);

        $manager->persist($utilisateur);
        $manager->persist($apprenant);

        $message=(new\Swift_Message)
                ->setSubject('Orange Digital Center, SONATEL ACADEMY')
                ->setFrom('xxxxx@gmail.com')
                ->setTo($utilisateur->getEmail())
                ->setBody("Bienvenue cher apprenant vous avez intégré la nouvelle promotion de la première école de codage gratuite du Sénégal, veuillez utiliser ce nom d'utilisateur: ".$utilisateur->getUsername()." et ce mot de passe : ".$password." par defaut pour se connecter");
        $mailer->send($message);
        $promo->addGroupe($groupe);
        //dd($promo);
        $errors = $validator->validate($promo);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, "json");
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($groupe);
        $manager->persist($promo);
        $manager->flush();
        return $this->json($serializer->normalize($promo), Response::HTTP_CREATED);
    }
}