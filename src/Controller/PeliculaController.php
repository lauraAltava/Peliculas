<?php

namespace App\Controller;

use App\Entity\Libro;
use App\Entity\Pelicula;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PeliculaController extends AbstractController
{
    private $peliculas = [

        1 => ["nombre" => "Resplandor", "año" => "1980", "director" => "Stanley Kubrik"],

        2 => ["nombre" => "It", "año" => "1990", "director" => "Tommy Lee Wallace"],

        3 => ["nombre" => "Eclipse Total", "año" => "1995", "director" => "Taylor Hackford"],

        4 => ["nombre" => "It", "año" => "2017", "director" => "Andres Muschietti"],

        5 => ["nombre" => "Pet Sematary", "año" => "1989", "director" => "Mary Lambert"],

        6 => ["nombre" => "Pet Sematary", "año" => "2019", "director" => "Kevin Kölsch"],

    ]; 
    #[Route('/pelicula/nueva', name: 'nueva_pelicula')]
    public function nuevo(ManagerRegistry $doctrine, Request $request){
        $contacto = new Pelicula();

        $formulario = $this->createForm(ContactoType::class, $contacto);

   
            $formulario->handleRequest($request);

            if($formulario->isSubmitted() && $formulario->isValid()){
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager -> persist($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_pelicula', 
                ["codigo" => $contacto->getId()]);
            }
        
        return $this->render('peliculas/nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }

    #[Route('/pelicula/editar/{codigo}', name:"editar_pelicula", 
    requirements:["codigo"=>"\d+"])]

    public function editar(ManagerRegistry $doctrine, Request $request, SessionInterface $session, 
    $codigo, SluggerInterface $slugger){
        $user = $this->getUser();
        
        if ($user){
        $repositorio = $doctrine->getRepository(Pelicula::class);
        $pelicula= $repositorio->find($codigo);

        if($pelicula){
            $formulario = $this->createForm(PeliculaType::class, $pelicula);
            $formulario->handleRequest($request);
        }
           

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData();
            $file = $formulario->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        
                // Move the file to the directory where images are stored
                try {
        
                    $file->move(
                        $this->getParameter('images_directory'), $newFilename
                    );
                   
                } catch (FileException $e) {
                   
                }
                $contacto->setFile($newFilename);
            }
               
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($contacto);
            $entityManager->flush();
        }
        return $this->render('peliculas/nuevo.html.twig', array(
            'formulario' => $formulario->createView()));
        

        }else{

            $url=$this->generateUrl('editar_pelicula', ['codigo' => $codigo]);
            $session->set('enlace', $url);
            return $this->redirectToRoute('app_login');
        }
}
#[Route('/pelicula/insertarConLibro', name: 'insertar_con_libro_pelicula')]

public function insertarConLibro(ManagerRegistry $doctrine): Response{
    $entityManager = $doctrine->getManager();
    $libro = new Libro();

    $libro->setNombre("It");
    $pelicula = new Pelicula();



    $pelicula->setNombre("Insercion de una prueba con pelicula");
    $pelicula->setAño("2017");
    $pelicula->setDirector("Kubrick");
    $pelicula->setLibro($libro);
  

    $entityManager->persist($libro);
    $entityManager->persist($pelicula);

    $entityManager->flush();
    return $this->render('ficha_pelicula.html.twig',[
        'pelicula' => $pelicula
    ]);
    
}  
#[Route('/pelicula/{codigo}', name: 'ficha_pelicula')]
public function ficha(ManagerRegistry $doctrine, $codigo): Response{
    $repositorio = $doctrine->getRepository(pelicula::class);
    $pelicula = $repositorio->find($codigo);

    return $this->render('ficha_pelicula.html.twig', [
        'pelicula' => $pelicula
    ]);
}
#[Route('/pelicula/buscar/{texto}', name: 'buscar_pelicula')]
public function buscar(ManagerRegistry $doctrine, $texto): Response{
    $repositorio = $doctrine->getRepository(pelicula::class);

    $peliculas = $repositorio->findByName($texto);

    return $this->render('lista_peliculas.html.twig', [
        'peliculas' => $peliculas
    ]);
}
#[Route('/pelicula/update/{id}/{nombre}', name: 'modificar_pelicula')]

public function update(ManagerRegistry $doctrine, $id, $nombre): Response{
    $entityManager = $doctrine->getManager();
    $repositorio = $doctrine->getRepository(pelicula::class);
    $pelicula = $repositorio->find($id);
    if ($pelicula){
        $pelicula->setNombre($nombre);
        try{
            $entityManager->flush();
            return $this->render('ficha_pelicula.html.twig', [
                'pelicula' => $pelicula
            ]);
        }catch (\Exception $e){
            return new Response("Error insertando objetos");
        }
    }else
        return $this->render('ficha_pelicula.html.twig', [
            'pelicula' => null
        ]);
}
#[Route('/pelicula/delete/{id}', name: 'eliminar_pelicula')]

public function delete(ManagerRegistry $doctrine, $id): Response{
    $entityManager = $doctrine->getManager();
    $repositorio = $doctrine->getRepository(pelicula::class);
    $pelicula = $repositorio->find($id);
    if ($pelicula){
        try{
            $entityManager->remove($pelicula);
            $entityManager->flush();
            return new Response("pelicula eliminado");
        }catch (\Exception $e){
            return new Response("Error eliminado objeto");
        }
    }else
        return $this->render('ficha_pelicula.html.twig', [
            'pelicula' => null
        ]);

    } 


}
