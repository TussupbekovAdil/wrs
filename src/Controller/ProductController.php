<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Team;
use App\Form\TeamType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\MemberType;
use App\Entity\User;
use App\Service\ProductService;

class ProductController extends AbstractController
{
    public function index()
    {
        return $this->render('product/main.html.twig');
    }
    
    public function team()
    {
        $teams = $this->getDoctrine()
        ->getRepository(Team::class)
        ->findAll();
        
        return $this->render('product/team.html.twig', [
            'teams' => $teams,
        ]);
    }
    
    public function createTeam(Request $request)
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_product_panel_team');
        }
        
        return $this->render('product/create_team.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    public function manage(Request $request)
    {
        $team = $this->getDoctrine()
        ->getRepository(Team::class)
        ->find($request->get('id'));
        
        return $this->render('product/team_manage.html.twig', [
            'team' => $team,
        ]);
    }
    
    public function addMember(Request $request, ProductService $service)
    {
        $team = $this->getDoctrine()
        ->getRepository(Team::class)
        ->find($request->get('id'));
        
        $form = $this->createForm(MemberType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            $user = $this->getDoctrine()->getRepository(User::class)->find(
                $request->request->get('member')['member_id']
            );
            
            if (!$service->addMemberToTeam($user, $team)) {
                throw new \Exception();
            }
            
            return $this->redirectToRoute('app_product_panel_team_manage', ['id' => $team->getId()]);
        }
        
        return $this->render('product/team_manage_add_member.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }
}
