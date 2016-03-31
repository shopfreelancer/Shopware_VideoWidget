<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class SfVideoWidgetControllerTest extends Enlight_Components_Test_Controller_TestCase
{
    protected static $ensureLoadedPlugins = array(
        'SfVideoWidget' => array(
        )
    );

    protected $plugin;


    public function setUp()
    {
        parent::setUp();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        /** @var Shopware_Plugins_Backend_SfVideoWidget_Bootstrap $plugin */
        $this->plugin = Shopware()->Plugins()->Backend()->SfVideoWidget();
    }

    public function Plugin()
    {
        return $this->plugin;
    }

    public function testCanCreateInstance()
    {

        $this->assertInstanceOf('Shopware_Plugins_Backend_SfVideoWidget_Bootstrap', $this->Plugin());
    }

    /**
     * get test data
     */
    public function getImgPathTestData()
    {
        $sql = "SELECT path FROM s_media ORDER BY RAND() LIMIT 1";
        $img_path = Shopware()->Db()->fetchOne($sql);
        return $img_path;
    }

    /**
     * check if backend controller delivers full_img_path for valid $_POST request
     * Send $_POST to controller and check json response
     *
     */
    public function testBackendControllerAction()
    {

        $this->Request()

        ->setModuleName('backend')
            ->setControllerName('SfVideoWidget')
            ->setActionName('getFullImgPath')
            ->setPost(array('img_path' => $this->getImgPathTestData()))->setDispatched(true);

        $this->dispatch();

        $data  = $this->View()->getAssign();

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['full_img_path']);

    }
}