<?php
class Shopware_Controllers_Backend_SfVideoWidget extends Shopware_Controllers_Backend_ExtJs
{

    public function getFullImgPathAction()
    {
        try {
        $request = $this->Request();

        if (!$request->has('img_path')) {
            $this->Response()->setBody(json_encode(array("success" => false)));
        }

        //$thumbnailFormat = Shopware()->Config()->getByNamespace('ItsKheEkwWidget', 'thumbnailFormat');
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $url          = $mediaService->getUrl($request->getParam("img_path"));

        $this->View()->assign(array('success' => true, "full_img_path" => $url));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

}