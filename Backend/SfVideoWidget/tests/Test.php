<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 * @runInSeparateProcess
 */
class SfVideoWidgetTest extends Shopware\Components\Test\Plugin\TestCase
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
     * thumbnail picture. Assumption: albumId 2 has thumbnails of Size specified in Plugin Config.
     */
    public function getImgIdTestData()
    {
        $albumId = Shopware()->Config()->getByNamespace('SfVideoWidget', 'albumId');
        $sql = "SELECT id FROM s_media WHERE albumID = ? LIMIT 1";
        $imgId = Shopware()->Db()->fetchOne($sql,array($albumId));
        return $imgId;
    }

    /**
     * Mock object for Frontend Emotion Element from Plugin
     */
    public function testFilterFindsThumbnailImage()
    {
        $arguments = new Enlight_Event_EventArgs;
        $data["khe_img_id"] = $this->getImgIdTestData();
        $arguments->setReturn($data);

        $return = $this->Plugin()->kheEmotionFilterCallback($arguments);
        $this->assertNotEmpty($return["imagePath"]);

        /**
         * check if thumbnail exists -  img name has 280x280 value from Plugin Config in its name
         */
        $thumbnailFormat = Shopware()->Config()->getByNamespace('SfVideoWidget', 'thumbnailFormat');
        $thumbNailImage = substr($return["imagePath"] ,(strrpos($return["imagePath"],".") - strlen($thumbnailFormat)), strlen($thumbnailFormat));
        $this->assertEquals($thumbnailFormat, $thumbNailImage);
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

}