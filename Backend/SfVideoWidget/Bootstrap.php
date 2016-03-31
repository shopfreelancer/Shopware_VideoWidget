<?php
class Shopware_Plugins_Backend_SfVideoWidget_Bootstrap
    extends Shopware_Components_Plugin_Bootstrap
{
    protected $albumId;
    protected $albumName = "VorschauVideos";
    /**
     * @return bool
     */
    public function install()
    {
        $this->setAlbumId();
        $this->createConfigForm();
        $this->createVideoWidget();
        $this->subscribeEvents();

        return true;
    }

    public function uninstall()
    {
        return true;
    }


    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'author'=> 'Shop Freelancer',
            'copyright' => 'Copyright (c) 2016, Shop Freelancer',
            'label' => $this->getLabel(),
            'description' => "Erstellt ein Video Widget für die Einkaufswelten. Klick auf Vorschaubild öffnet eine responsive Youtube / Vimeo Lightbox",
            'support' => 'http://www.shop-freelancer.de',
            'link' => 'http://www.shop-freelancer.de',
            'revision' => '1'
        );
    }

    public function getLabel()
    {
        return "Video Einkaufswelten Elemente";
    }

    public function getVersion()
    {
        return "1.0.0";
    }

    public function onGetBackendSfVideoWidgetController(Enlight_Event_EventArgs $args)
    {
        //$this->get('template')->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . '/Controllers/Backend/SfVideoWidget.php';
    }

    public function createConfigForm()
    {
        $form = $this->Form();
        $form->setElement('text', 'thumbnailFormat', array(
            'label'    => 'Thumbnailgröße der Bilder',
            'description' => 'Im Format 280x280 eintragen. Die Bilder müssen am besten einem Album mit diesen Bildgrößen zugewiesen sein.',
            'required' => true,
            'value'    => '280x280'
        ));

        $form->setElement('text', 'albumId', array(
            'value'    => $this->albumId,
            'label'    => 'albumId',
        ));

        $this->createNewMediaAlbum();

        return true;
    }

    /**
     * create one Emotion widget
     * @return bool
     */
    public function createVideoWidget()
    {
        $component = $this->createEmotionComponent(array(
            'name' => 'VideoWidget',
            'template' => 'sf',
            'xtype' => 'sf-video',
            'description' => 'Bindet ein Widget ein zur Anzeige von Überschrift und Vorschaubild. Click öffnet ein responsive Modal-Window mit dem Video.'
        ));

        $component->createTextField(array(
            'name' => 'sf_headline1',
            'fieldLabel' => 'Headline',
            'supportText' => 'Gelbe Überschrift',
            'allowBlank' => true
        ));

        $component->createTextField(array(
            'name' => 'sf_headline2',
            'fieldLabel' => 'Headline 2',
            'supportText' => 'Kleinere Überschrift',
            'allowBlank' => true
        ));


        $component->createDisplayField(array(
            'name' => 'sf_img',
            'fieldLabel' => 'Hinterlegtes Bild',
            'allowBlank' => true
        ));


        $component->createHiddenField(array(
            'name' => 'sf_img_id',
            'allowBlank' => true
        ));

        $component->createHiddenField(array(
            'name' => 'albumId',
            'defaultValue' => $this->albumId
        ));


        $component->createTextField(array(
            'name' => 'sf_video',
            'fieldLabel' => 'Link zu Video (youtube / vimeo)',
            'supportText' => 'URL im Format https://vimeo.com/42487034 bzw. https://youtu.be/LiZE6Qf48DI hinterlegen.',
            'allowBlank' => true
        ));
        return true;
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents()
    {

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Campaign',
            'extendsEmotionTemplates'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SfVideoWidget',
            'onGetBackendSfVideoWidgetController'
        );

        $this->subscribeEvent(
            'Shopware_Controllers_Widgets_Emotion_AddElement',
            'sfVideoEmotionFilterCallback'
        );

        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Javascript',
            'addJsFiles'
        );

        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'addLessFiles'
        );
    }

    /**
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function sfVideoEmotionFilterCallback(Enlight_Event_EventArgs $arguments)
    {

        $data = $arguments->getReturn();
        $thumbnailFormat = Shopware()->Config()->getByNamespace('SfVideoWidget', 'thumbnailFormat');


        if(!empty($data["sf_img_id"])){

                $mediaService = Shopware()->Container()->get('shopware_media.media_service');

                $modelManager = Shopware()->Container()->get('models');
                $repo = $modelManager->getRepository('Shopware\Models\Media\Media');


                $mediaModel = $repo->find($data["sf_img_id"]);

                if($mediaModel !== null){
                    $thumbsModel = $mediaModel->getThumbnails();
                    $data['image']['source'] = $mediaService->getUrl($data["sf_img"]);
                    if(array_key_exists($thumbnailFormat,$thumbsModel)){
                       $data['imagePath'] = $mediaService->getUrl($thumbsModel[$thumbnailFormat]);
                    } else {
                        $data['imagePath'] = $mediaService->getUrl($mediaModel->getPath());
                    }
                }
                $modelManager->flush();
            }


        return $data;
    }

    /**
     * Provide the file collection for js files
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addJsFiles(Enlight_Event_EventArgs $args)
    {
        $jsFiles = array(__DIR__ . '/Views/responsive/frontend/_public/src/js/script.js');
        return new Doctrine\Common\Collections\ArrayCollection($jsFiles);
    }

    /**
     * Provide the file collection for Less
     */
    public function addLessFiles(Enlight_Event_EventArgs $args)
    {
        $less = new \Shopware\Components\Theme\LessDefinition(
        //configuration
            array(),
            //less files to compile
            array(
                __DIR__ . '/Views/responsive/frontend/_public/src/less/all.less'
            ),

            //import directory
            __DIR__
        );

        return new Doctrine\Common\Collections\ArrayCollection(array($less));
    }

    /**
     * Album may exist already from previous installation
     */
    public function setAlbumId()
    {
        $sql = 'SELECT id from s_media_album WHERE name = "'.$this->albumName.'"';
        $oldAlbumId  = Shopware()->Db()->fetchOne($sql);

        if(!empty($oldAlbumId)) {
            $this->albumId = $oldAlbumId;
        } else {
            $this->albumId = $this->createNewMediaAlbum();
        }
    }

    /**
     * creates new media album if not exists
     * @return string
     */
    public function createNewMediaAlbum(){

        if(!empty($this->albumId)){
            return;
        }
        /** @var ModelManager $em */
        $em = $this->get('models');


        $album = new Shopware\Models\Media\Album();

        $em->persist($album);

        $album->setName($this->albumName);
        $album->setPosition(100);

        $settings = new Shopware\Models\Media\Settings();
        $settings->setAlbum($album);

        $createThumbnails = 1;
        $thumbnailSizes = "280x280";
        $thumbnailHighDpi = true;
        $thumbnailQuality = "90";
        $thumbnailHighDpiQuality = "90";
        $icon = 'sprite-blue-folder';


        $settings->setCreateThumbnails($createThumbnails);
        $settings->setThumbnailSize($thumbnailSizes);
        $settings->setThumbnailHighDpi($thumbnailHighDpi);
        $settings->setThumbnailQuality($thumbnailQuality);
        $settings->setThumbnailHighDpiQuality($thumbnailHighDpiQuality);
        $settings->setIcon($icon);

        $data['settings'] = $settings;
        $album->fromArray($data);

        try{
            $em->flush($album);
            $em->flush($album->getSettings());
        } catch(Exception $e){
            return "Error creating Media Album";
        }

        return $album->getId();

    }
}