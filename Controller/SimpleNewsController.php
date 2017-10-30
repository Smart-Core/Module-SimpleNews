<?php

namespace SmartCore\Module\SimpleNews\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use SmartCore\Bundle\CMSBundle\Module\NodeTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SimpleNewsController extends Controller
{
    use NodeTrait;

    /**
     * Список новостей постранично.
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page = null)
    {
        $this->node->addFrontControl('create')
            ->setTitle('Добавить новость')
            ->setUri($this->generateUrl('smart_module.news_admin.create'));

        if (null === $page) {
            $page = $request->query->get('page', 1);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('SimpleNewsModuleBundle:News')->getFindAllEnablesQuery()
        ));
        $pagerfanta->setMaxPerPage($this->node->getParam('items_per_page', 10));

        try {
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        if ($page > 1) {
            $this->get('cms.breadcrumbs')->add(null, $this->get('translator')->trans('Page').': '.$page);
        }

        return $this->get('twig')->render('SimpleNewsModuleBundle::news.html.twig', ['news' => $pagerfanta]);
    }

    /**
     * Отображение заданной новости.
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function itemAction($slug)
    {
        $item = $this->getDoctrine()->getRepository('SimpleNewsModuleBundle:News')->findOneBy(['slug' => $slug]);

        if (empty($item)) {
            throw $this->createNotFoundException('News not found');
        }

        $this->get('cms.breadcrumbs')->add($item->getSlug(), $item->getTitle());

        $this->node->addFrontControl('edit')
            ->setTitle('Редактировать новость')
            ->setUri($this->generateUrl('smart_module.news_admin.edit', ['id' => $item->getId()]));

        $this->node->addFrontControl('create')
            ->setIsDefault(false)
            ->setTitle('Добавить новость')
            ->setUri($this->generateUrl('smart_module.news_admin.create'));

        return $this->get('twig')->render('SimpleNewsModuleBundle::item.html.twig', ['item' => $item]);
    }
}
