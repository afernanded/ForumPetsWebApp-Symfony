<?php

namespace App\Controller;

use App\Entity\Comentarios;
use App\Entity\Posts;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->getRepository(Posts::class)->mostrarTodosPost();

        $user = $this->getUser();
        $arrayComentarios = $entityManager->getRepository(Comentarios::class)->findBy(['user' => $user]);


        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            3 /*limit per page*/
        );

        //$postId1 = $entityManager->getRepository(Posts::class)->find(1);
        //$arrayLikes0 = $entityManager->getRepository(Posts::class)->findBy(['likes'=>'']);
        //$postNuevosUsers = $entityManager->getRepository(Posts::class)->findOneBy(['titulo'=>'Hola']);

        return $this->render('dashboard/index.html.twig', [
            'pagination' => $pagination,
            'arrayComentarios' => array_slice(array_reverse($arrayComentarios), 0, 5),
        ]);
    }
}
