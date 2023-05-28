<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Answer;
use App\Entity\Question;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    private QuestionRepository $questionRepository;
    // private ChartBuilderInterface $chartBuilder;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
        // $this->chartBuilder = $chartBuilder;
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $latestQuestions = $this->questionRepository
            ->findLatest();
        $topVoted = $this->questionRepository
            ->findTopVoted();
        
        return $this->render('admin/index.html.twig', [
            'latestQuestions' => $latestQuestions,
            'topVoted' => $topVoted,
            // 'chart' => $this->createChart(),
        ]);

        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(Answer::class)->generateUrl());
        
        // return $this->redirect($adminUrlGenerator->setController(Answer::class)->generateUrl());
        // return $this->redirect($adminUrlGenerator->setController(Topic::class)->generateUrl());
        // return $this->redirect($adminUrlGenerator->setController(User::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony 6 Admin');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user');
        }

        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatarUrl())
            ->setMenuItems([
                MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl(
                    'app_profile_show'
                ))
            ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Questions', 'fa fa-question-circle', Question::class);
        yield MenuItem::linkToCrud('Answers', 'fa fa-comments', Answer::class);
        yield MenuItem::linkToCrud('Topics', 'fa fa-folder', Topic::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', $this->generateUrl('app_homepage'));
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }



    public function configureActions(): Actions
    {
        return parent::configureActions()
			->add(Crud::PAGE_INDEX, Action::DETAIL);
    }


    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDefaultSort([
                'id' => 'DESC',
            ])
            ->overrideTemplate('crud/field/id', 'admin/field/id_with_icon.html.twig');
    }

    // private function createChart(): Chart
    // {
    //     $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

    //     $chart->setData([
    //         'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    //         'datasets' => [
    //             [
    //                 'label' => 'My First dataset',
    //                 'backgroundColor' => 'rgb(255, 99, 132)',
    //                 'borderColor' => 'rgb(255, 99, 132)',
    //                 'data' => [0, 10, 5, 2, 20, 30, 45],
    //             ],
    //         ],
    //     ]);

    //     $chart->setOptions([
    //         'scales' => [
    //             'y' => [
    //                 'suggestedMin' => 0,
    //                 'suggestedMax' => 100,
    //             ],
    //         ],
    //     ]);

    //     return $chart;
    // }
}
