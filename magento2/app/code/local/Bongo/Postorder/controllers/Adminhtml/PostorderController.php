<?php

class Bongo_Postorder_Adminhtml_PostorderController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('postorder/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('postorder/postorder')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('postorder_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('postorder/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('postorder/adminhtml_postorder_edit'))
                    ->_addLeft($this->getLayout()->createBlock('postorder/adminhtml_postorder_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('postorder')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function approveAction() {
        if ($this->getRequest()->getParam('id')) {

            try {
                $postorder = Mage::getSingleton('postorder/postorder')
                        ->load($this->getRequest()->getParam('id'))
                        ->setStatus(1)
                        ->setUpdateTime(now())
                        ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('postorder')->__('Testimonial was successfully approved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/');
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('postorder')->__('Unable to find item to update'));
        $this->_redirect('*/*/');
    }

    public function disapproveAction() {
        if ($this->getRequest()->getParam('id')) {

            try {
                $postorder = Mage::getSingleton('postorder/postorder')
                        ->load($this->getRequest()->getParam('id'))
                        ->setStatus(2)
                        ->setUpdateTime(now())
                        ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('postorder')->__('Testimonial was successfully disabled'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/');
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('postorder')->__('Unable to find item to update'));
        $this->_redirect('*/*/');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $value = $data['filename'];

            if (is_array($value) && !empty($value['delete'])) {
                $data['image'] = '';
            }

            $image_path = Mage::getBaseDir('media') . DS . "testimonials";

            // create vendor media dir
            if (!file_exists($image_path)) {
                mkdir($image_path);
                chmod($image_path, 0777);
            }


            $model = Mage::getModel('postorder/postorder');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();

                if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                    try {
                        /* Starting upload */
                        $uploader = new Varien_File_Uploader('filename');

                        // Any extention would work
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode
                        // false -> get the file directly in the specified folder
                        // true -> get the file in the product like folders
                        //	(file.jpg will go in something like /media/f/i/file.jpg)
                        $uploader->setFilesDispersion(false);

                        // We set media as the upload dir
                        $logo_name = $model->getPostorderId() . "_" . $_FILES["filename"]["name"];
                        $uploader->save($image_path . DS, $logo_name);

                        $model->setImage('testimonials/' . $logo_name);
                        $model->save();
                    } catch (Exception $e) {
                        
                    }
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('postorder')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('postorder')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('postorder/postorder');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $postorderIds = $this->getRequest()->getParam('postorder');
        if (!is_array($postorderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($postorderIds as $postorderId) {
                    $postorder = Mage::getModel('postorder/postorder')->load($postorderId);
                    $postorder->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($postorderIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $postorderIds = $this->getRequest()->getParam('postorder');
        if (!is_array($postorderIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($postorderIds as $postorderId) {
                    $postorder = Mage::getSingleton('postorder/postorder')
                            ->load($postorderId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($postorderIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'postorder.csv';
        $content = $this->getLayout()->createBlock('postorder/adminhtml_postorder_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'postorder.xml';
        $content = $this->getLayout()->createBlock('postorder/adminhtml_postorder_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}