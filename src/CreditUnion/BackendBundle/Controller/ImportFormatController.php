<?php

namespace CreditUnion\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use CreditUnion\BackendBundle\Entity\ImportFormat;
use CreditUnion\FrontendBundle\Entity\Client;
use CreditUnion\BackendBundle\Form\ImportFormatType;

/**
 * ImportFormat controller.
 *
 * @Route("/ImportFormat")
 */
class ImportFormatController extends Controller
{

    /**
     * Displays a form to create/edit an existing Importformat entity.
     *
     * @Route("/create/{branchId}", name="cr_backend_importformat_create")
     */
    public function createEditAction(Request $request, $branchId)
    {
        $entity = new ImportFormat();
        $entity->setDateFormat('d-M-Y');
        $entity->setDelimiterCsv(',');

        $em = $this->getDoctrine()->getManager();
        $branch = $em->getRepository('CreditUnionFrontendBundle:Branch')->find($branchId);
        $entity->setBranch($branch);

        return $this->createAndEdit($request, $entity, 'create');
    }

    /**
     * Displays a form to create/edit an existing Importformat entity.
     *
     * @Route("/edit/{id}", name="cr_backend_importformat_edit")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CreditUnionBackendBundle:ImportFormat')->find($id);

        return $this->createAndEdit($request, $entity, 'edit');
    }

    protected function createAndEdit($request, $entity, $type)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new ImportFormatType, $entity);
        $this->matchField($entity, $request);

        if ($request->getMethod() == 'POST') {

            $form->bind($request->get('creditunion_backendbundle_imporformat'));
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                if ($type == 'create') {
                    return $this->redirect($this->generateUrl('cr_backend_importformat_edit', array('id' => $entity->getId())));
                }
            }
        }

        $defaultImportColumnNames = Client::getImportColumnNames();
        $importColumnNames = $this->createImportColumnNames($entity, $defaultImportColumnNames);

        return $this->render('CreditUnionBackendBundle:ImportFormat:createAndEdit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'importColumnNames' => $importColumnNames,
                    'type' => $type
        ));
    }

    /**
     * Set match field from the request
     * 
     * @param \CreditUnion\BackendBundle\Entity\ImportFormat $entity
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function matchField(ImportFormat $entity, Request $request)
    {
        $format = $request->get('format');
        $sortFormat = array();
        
        if (!empty($format)) {
            foreach ($format as $key => $column) {
                if (isset($column['enabled'])) {
                    $sortFormat[$column['order']] = $key;
                }
            }
            $sortFormat = array_values($sortFormat);

            $entity->setMatchField($sortFormat);
        }
    }

    /**
     * set importColumnNmaes array from the entity field match
     * 
     * @param \CreditUnion\BackendBundle\Entity\ImportFormat $entity
     * @param array $importColumnNames
     */
    protected function createImportColumnNames(ImportFormat $entity, array $importColumnNames)
    {
        $newImportCN = array();
        if (!empty($entity->getMatchField())) {
            foreach ($entity->getMatchField() as $matchField) {
                $newImportCN[$matchField] = $importColumnNames[$matchField];
                $newImportCN[$matchField]['enabled'] = true;
                unset($importColumnNames[$matchField]);
            }

            foreach ($importColumnNames as $key => $value) {
                $newImportCN[$key] = $value;
            }
            return $newImportCN;
        }
        return $importColumnNames;
    }

    /**
     * Deletes a imporformat entity.
     *
     * @Route("/{id}", name="cr_backend_imporformat_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CreditUnionBeckendBundle:imporFormat')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find imporformat entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cr_backend_branch_index'));
    }

    /**
     * Creates a form to delete a imporformat entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('cr_backend_imporformat_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
