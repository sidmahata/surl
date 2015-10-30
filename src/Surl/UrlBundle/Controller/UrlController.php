<?php

namespace Surl\UrlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Surl\UrlBundle\Entity\Url;
use Surl\UrlBundle\Form\UrlType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Url controller.
 *
 */
class UrlController extends Controller
{


    /**
     * Lists all Url entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
//
//        get the session username
        $username = $this->get('security.token_storage')->getToken()->getUser();

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            //        find all records according to session username
            $entities = $em->getRepository('SurlUrlBundle:Url')->findByAuthor(
                $username -> getUsername()
            );
        }else{
            $entities = 'no data yet';
        }
//


//        $repository = $this->getDoctrine()
//            ->getRepository('SurlUrlBundle:Url');
//
//// createQueryBuilder automatically selects FROM AppBundle:Product
//// and aliases it to "p"
//        $query = $repository->createQueryBuilder('p')
//            ->where('p.author = :author')
//            ->setParameter('author', $username -> getUsername())
//            ->getQuery();
//
//        $entities = $query->getResult();


        return $this->render('SurlUrlBundle:Url:index.html.twig', array(
            'entities' => $entities,
            'user' => $username
        ));
    }
    /**
     * Creates a new Url entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Url();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
//        sets the author field to session username
        $entity->setAuthor($this->get('security.token_storage')->getToken()->getUser());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                // creating the ACL
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = ObjectIdentity::fromDomainObject($entity);
                $acl = $aclProvider->createAcl($objectIdentity);

                // retrieving the security identity of the currently logged-in user
                $tokenStorage = $this->get('security.token_storage');
                $user = $tokenStorage->getToken()->getUser();
                $securityIdentity = UserSecurityIdentity::fromAccount($user);

                // grant owner access
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                $aclProvider->updateAcl($acl);
            }



            return $this->redirect($this->generateUrl('url_show', array('id' => $entity->getId())));
        }

        return $this->render('SurlUrlBundle:Url:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Url entity.
     *
     * @param Url $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Url $entity)
    {
        $form = $this->createForm(new UrlType(), $entity, array(
            'action' => $this->generateUrl('url_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Url entity.
     *
     */
    public function newAction()
    {
        $entity = new Url();
        $form   = $this->createCreateForm($entity);

        return $this->render('SurlUrlBundle:Url:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Url entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SurlUrlBundle:Url')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Url entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SurlUrlBundle:Url:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Url entity.
     *
     */
    public function editAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SurlUrlBundle:Url')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Url entity.');
        }

        $userrole = $this->get('security.token_storage')->getToken()->getUser();

//        check if user is admin - if yes then bypass authorization check
        if(!in_array("ROLE_ADMIN", $userrole -> getRoles())){
            $authorizationChecker = $this->get('security.authorization_checker');

            // check for edit access
            if (false === $authorizationChecker->isGranted('EDIT', $entity)) {
                throw new AccessDeniedException();
            }
        }

        //        After acl is passed
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SurlUrlBundle:Url:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Url entity. FOR ROLE_ADMIN
     *
     */
    public function admineditAction($id)
    {

//        After acl is passed
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SurlUrlBundle:Url')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Url entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SurlUrlBundle:Url:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Url entity.
    *
    * @param Url $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Url $entity)
    {
        $form = $this->createForm(new UrlType(), $entity, array(
            'action' => $this->generateUrl('url_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Url entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SurlUrlBundle:Url')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Url entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('url_edit', array('id' => $id)));
        }

        return $this->render('SurlUrlBundle:Url:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Url entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SurlUrlBundle:Url')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Url entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('url'));
    }

    /**
     * Creates a form to delete a Url entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('url_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
