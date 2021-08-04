<?php

namespace App\Controller;

use App\Entity\Comentarios;
use App\Entity\Posts;
use App\Form\ComentarioType;
use App\Form\PostsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostsController extends AbstractController
{
    /**
     * @Route("/posts", name="posts")
     */
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('foto')->getData();

            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Error al subir la imagen');
                }
                $post->setFoto($newFilename);
            }
            $user = $this->getUser();
            $post->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);

            $entityManager->flush();
            $this->addFlash('Exito', Posts::POST_CORRECTO);
            return $this->redirectToRoute('posts');
        }



        return $this->render('posts/index.html.twig', [
            'controller_name' => 'Nuevo Post',
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/verpost/{id}", name="verpost")
     */
    public function verPost(Request $request, $id){
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $comentarios = new Comentarios();
        $formularioComentarios = $this->createForm(ComentarioType::class,$comentarios);
        $formularioComentarios->handleRequest($request);
        $post = $entityManager->getRepository(Posts::class)->find($id);
        $arrayComentarios = $entityManager->getRepository(Comentarios::class)->findBy(['posts' => $id]);
        if ($formularioComentarios->isSubmitted() && $formularioComentarios->isValid()) {
            $textoComentario = $formularioComentarios['Comentario']->getData();
            $comentarios->setComentario($textoComentario);
            $comentarios->setUser($user);
            $comentarios->setPosts($post);
            $comentarios->setFechaPublicacion(new \DateTime());
            $entityManager->persist($comentarios);
            $entityManager->flush();
            return $this->redirectToRoute('verpost', ['id' => $id]);
        }
        $post->getId();
        return $this->render('posts/verPost.html.twig', [
            'post' => $post,
            'formulario' => $formularioComentarios->createView(),
            'arrayComentarios' => array_reverse($arrayComentarios),
        ]);
    }

    /**
     * @Route("/misposts", name="misposts")
     */
    public function misPosts(){
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $posts = $entityManager->getRepository(Posts::class)->findBy(['user'=>$user]);
        return $this->render('posts/misPosts.html.twig',[
            'posts' => $posts
        ]);
    }


    /**
     * @Route("/likes", options={"expose"=true}, name="likes")
     */
    public function likes(Request $request) {
        if ($request->isXmlHttpRequest()){
            $user = $this->getUser();
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            $post = $entityManager->getRepository(Posts::class)->find($id);
            $likes = $post->getLikes();
            $likes .= $user->getId().',';
            $post->setLikes($likes);
            $entityManager->flush();
            return new JsonResponse(['likes' => $likes]);

        }else{
            throw new \Exception('Error en el tipo de solicitud');
        }
    }


    /**
     * @Route("/dislike", options={"expose"=true}, name="dislike")
     */
    public function dislike(Request $request) {
        if ($request->isXmlHttpRequest()){
            $user = $this->getUser();
            $entityManager = $this->getDoctrine()->getManager();
            $id = $request->request->get('id');
            $post = $entityManager->getRepository(Posts::class)->find($id);
            $likes = substr($post->getLikes(), $id, -2);
            $post->setLikes($likes);
            $entityManager->flush();
            return new JsonResponse(['likes' => $likes]);

        }else{
            throw new \Exception('Error en el tipo de solicitud');
        }
    }



}