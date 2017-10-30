<?php

namespace SmartCore\Module\SimpleNews\Controller;

use Smart\CoreBundle\Controller\Controller;
use SmartCore\Module\SimpleNews\Entity\News;
use SmartCore\Module\SimpleNews\Entity\NewsInstance;
use SmartCore\Module\SimpleNews\Form\Type\NewsFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class NewsAdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine();

        $folderPath = null;
        foreach ($this->get('cms.node')->findByModule('SimpleNews') as $node) {
            $folderPath = $this->get('cms.folder')->getUri($node);

            break;
        }

        return $this->render('SimpleNewsModuleBundle:Admin:index.html.twig', [
            'folderPath' => $folderPath,
            'news'       => $em->getRepository('SimpleNewsModuleBundle:News')->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // @todo пока временная заглушка для экземпляров новостных лент.
        $newsInstance = $em->getRepository('SimpleNewsModuleBundle:NewsInstance')->findOneBy([]);

        if (empty($newsInstance)) {
            $newsInstance = new NewsInstance();
            $newsInstance->setName('Default news');
        }

        $news = new News();
        $news->setInstance($newsInstance);

        $form = $this->createForm(NewsFormType::class, $news);
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                $url = $request->query->has('redirect_to')
                    ? $request->query->get('redirect_to')
                    : $this->generateUrl('smart_module.news_admin');

                return $this->redirect($url);
            }

            if ($form->isValid()) {
                return $this->saveItemAndRedirect($form->getData(), 'smart_module.news_admin', 'Новость создана.');
            }
        }

        $folderPath = null;
        foreach ($this->get('cms.node')->findByModule('SimpleNews') as $node) {
            $folderPath = $this->get('cms.folder')->getUri($node);

            break;
        }

        return $this->render('SimpleNewsModuleBundle:Admin:create.html.twig', [
            'form'       => $form->createView(),
            'folderPath' => $folderPath,
        ]);
    }

    /**
     * @param  Request $request
     * @param  int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        $form = $this->createForm(NewsFormType::class, $this->getDoctrine()->getManager()->find('SimpleNewsModuleBundle:News', $id));
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);
        $form->add('delete', SubmitType::class, ['attr' => ['class' => 'btn btn-danger', 'onclick' => "return confirm('Вы уверены, что хотите удалить запись?')"]]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                $url = $request->query->has('redirect_to')
                    ? $request->query->get('redirect_to')
                    : $this->generateUrl('smart_module.news_admin');

                return $this->redirect($url);
            }

            if ($form->get('delete')->isClicked()) {
                $this->remove($form->getData(), true);
                $this->addFlash('success', 'Запись удалена');

                return $this->redirectToRoute('smart_module.news_admin');
            }

            if ($form->isValid()) {
                /** @var News $news */
                $news = $form->getData();
                $image = $news->getImage();

                $mc = $this->get('smart_media')->getCollection($news->getInstance()->getMediaCollection()->getId());

                // удаление файла.
                $_delete_ = $request->request->get('_delete_');
                if (is_array($_delete_)
                    and empty($image)
                    and isset($_delete_['image'])
                    and 'on' === $_delete_['image']
                ) {
                    $mc->remove($news->getImageId());
                    $news->setImageId(null);
                }

                if ($image instanceof UploadedFile) {
                    $newImageId = $mc->upload($image);
                    $oldImageId = $news->getImageId();

                    $news->setImageId($newImageId);
                    $mc->remove($oldImageId);
                }

                return $this->saveItemAndRedirect($news, 'smart_module.news_admin', 'Новость сохранена.');
            }
        }

        $itemPath = null;

        foreach ($this->get('cms.node')->findByModule('SimpleNews') as $node) {
            if ($folderPath = $this->get('cms.folder')->getUri($node)) {
                $itemPath = $this->generateUrl('smart_module.news.item', [
                    '_folderPath' => $folderPath,
                    'slug' => $form->getData()->getSlug(),
                ]);
            }
        }

        return $this->render('SimpleNewsModuleBundle:Admin:edit.html.twig', [
            'form'     => $form->createView(),
            'itemPath' => $itemPath,
        ]);
    }

    /**
     * @param int           $item
     * @param string        $redirect_to
     * @param string|null   $notice
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function saveItemAndRedirect($item, $redirect_to, $notice = null)
    {
        $this->persist($item, true);

        $this->addFlash('success', $notice);

        $request = $this->get('request_stack')->getCurrentRequest();

        $url = $request->query->has('redirect_to')
            ? $request->query->get('redirect_to')
            : $this->generateUrl($redirect_to);

        return $this->redirect($url);
    }
}
