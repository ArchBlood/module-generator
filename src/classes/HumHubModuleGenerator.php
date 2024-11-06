<?php

/**
 * @author Green Meteor
 * @link https://greenmeteor.net/
 * @copyright Copyright (c) 2024 Green Meteor Inc. 
 * @license https://marketplace.greenmeteor.net/legal#licenses
 */

/**
 * Class HumHubModuleGenerator
 *
 * A generator class for creating the file structure and essential files for a HumHub module.
 * This includes module configuration, assets, controllers, views, and packaging into a zip file.
 */
class HumHubModuleGenerator
{
    private $moduleName;
    private $moduleDescription;
    private $author;
    private $email;
    private $homepage;
    private $role;
    private $basePath;
    private $zipPath;

    /**
     * HumHubModuleGenerator constructor.
     *
     * @param string $moduleName Name of the module.
     * @param string $moduleDescription Description of the module.
     * @param string $author Author's name.
     */
    public function __construct($moduleName, $moduleDescription, $author, $email, $homepage, $role)
    {
        $this->moduleName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $moduleName));
        $this->moduleDescription = $moduleDescription;
        $this->author = $author;
        $this->email = $email;
        $this->homepage = $homepage;
        $this->role = $role;
        $this->basePath = "protected/modules/{$this->moduleName}";
        $this->zipPath = "{$this->moduleName}.zip";
    }

    /**
     * Generates the module by creating necessary files and directories.
     *
     * @return array Success message and path of the generated zip file.
     */
    public function generate()
    {
        try {
            $this->createDirectoryStructure();
            $this->generateModuleJson();
            $this->generateConfigPhp();
            $this->generateModulePhp();
            $this->generateController();
            $this->generateView();
            $this->generateAssets();
            $this->createZipArchive();

            return [
                'message' => "Module '{$this->moduleName}' has been generated successfully!",
                'zipPath' => $this->getZipPath()
            ];
        } catch (Exception $e) {
            $this->cleanup(); // Clean up if there's an error
            throw $e;
        }
    }

    /**
     * Returns the path of the generated zip file.
     *
     * @return string Zip file path.
     */
    public function getZipPath()
    {
        return $this->zipPath;
    }

    /**
     * Cleans up generated files and directories.
     * Should be called after the zip file has been downloaded.
     *
     * @return bool True if cleanup was successful, false otherwise.
     */
    public function cleanup()
    {
        $success = true;

        // Delete the module directory
        if (file_exists($this->basePath)) {
            $success &= $this->deleteDirectory($this->basePath);
        }

        // Delete the zip file
        if (file_exists($this->zipPath)) {
            $success &= unlink($this->zipPath);
        }

        return $success;
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @param string $dir Path to directory
     * @return bool True if deletion was successful, false otherwise
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    private function createDirectoryStructure()
    {
        $directories = [
            $this->basePath,
            "{$this->basePath}/assets",
            "{$this->basePath}/controllers",
            "{$this->basePath}/models",
            "{$this->basePath}/views",
            "{$this->basePath}/views/default",
            "{$this->basePath}/resources",
            "{$this->basePath}/resources/js",
            "{$this->basePath}/resources/css"
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Generates the module.json configuration file.
     */
    private function generateModuleJson()
    {
        $content = [
            'id' => $this->moduleName,
            'name' => ucfirst($this->moduleName),
            'description' => $this->moduleDescription,
            'keywords' => ['humhub', 'module'],
            'version' => '1.0.0',
            'humhub' => [
                'minVersion' => '1.16.0'
            ],
            'authors' => [
                [
                    'name' => $this->author,
                    'email' => $this->email,
                    'homepage' => $this->homepage,
                    'role' => $this->role,
                ]
            ]
        ];
        file_put_contents("{$this->basePath}/module.json", json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Generates the config.php file for the module.
     */
    private function generateConfigPhp()
    {
        $content = <<<PHP
<?php

return [
    'id' => '{$this->moduleName}',
    'class' => 'humhub\\modules\\{$this->moduleName}\\Module',
    'namespace' => 'humhub\\modules\\{$this->moduleName}',
    'events' => [
        [
            'class' => \\humhub\\modules\\admin\\widgets\\AdminMenu::class,
            'event' => \\humhub\\modules\\admin\\widgets\\AdminMenu::EVENT_INIT,
            'callback' => ['humhub\\modules\\{$this->moduleName}\\Module', 'onAdminMenuInit']
        ]
    ],
    'params' => [
        // Include any custom parameters your module might use here
    ]
];
PHP;
        file_put_contents("{$this->basePath}/config.php", $content);
    }

    /**
     * Generates the Module.php file with event handling for AdminMenu initialization.
     */
    private function generateModulePhp()
    {
        $content = <<<PHP
<?php

namespace humhub\modules\\{$this->moduleName};

use Yii;
use yii\helpers\Url;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\components\Module as BaseModule;

class Module extends BaseModule
{
    /**
     * Event handler to initialize the Admin Menu.
     *
     * @param \\yii\\base\\Event \$event Event data.
     */
    public static function onAdminMenuInit(\$event)
    {
        /** @var \\humhub\\modules\\admin\\widgets\\AdminMenu \$menu */
        \$menu = \$event->sender;

        \$menu->addEntry(new MenuLink([
            'label' => Yii::t('{$this->moduleName}Module.base', 'My Module'),
            'url' => Url::to(['/{$this->moduleName}/admin/index']),
            'icon' => Icon::get('folder'),
            'isActive' => (Yii::\$app->controller->module && Yii::\$app->controller->module->id == '{$this->moduleName}' && Yii::\$app->controller->id == '{$this->moduleName}'),
            'sortOrder' => 700,
            'isVisible' => true,
        ]));
    }

    /**
     * Initializes the module.
     */
    public function init()
    {
        parent::init();
    }
}
PHP;
        file_put_contents("{$this->basePath}/Module.php", $content);
    }

    /**
     * Generates a default controller with an index action.
     */
    private function generateController()
    {
        $content = <<<PHP
<?php

namespace humhub\modules\\{$this->moduleName}\controllers;

use humhub\modules\admin\components\Controller;
use Yii;

class DefaultController extends Controller
{
    /**
     * Renders the index view.
     *
     * @return string Rendered view.
     */
    public function actionIndex()
    {
        return \$this->render('index');
    }
}
PHP;
        file_put_contents("{$this->basePath}/controllers/DefaultController.php", $content);
    }

    /**
     * Generates a basic index view file.
     */
    private function generateView()
    {
        $content = <<<PHP
<?php

use humhub\modules\\{$this->moduleName}\assets\Assets;

Assets::register(\$this);

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Yii::t('{$this->moduleName}Module.base', '{$this->moduleName}') ?></strong>
    </div>
    <div class="panel-body">
        <div id="{$this->moduleName}-content">
            <?= \Yii::t('{$this->moduleName}Module.base', 'Welcome to the module template.') ?>
        </div>
    </div>
</div>
PHP;
        file_put_contents("{$this->basePath}/views/default/index.php", $content);
    }

    /**
     * Generates the assets configuration file.
     */
    private function generateAssets()
    {
        $content = <<<PHP
<?php

namespace humhub\modules\\{$this->moduleName}\assets;

use yii\web\AssetBundle;

class Assets extends AssetBundle
{
    public \$sourcePath = '@{$this->moduleName}/resources';

    public \$css = [
        'css/module.css'
    ];

    public \$js = [
        'js/module.js'
    ];

    public \$depends = [
        'humhub\assets\AppAsset'
    ];
}
PHP;
        file_put_contents("{$this->basePath}/assets/Assets.php", $content);
    }

    /**
     * Creates a zip archive of the generated module files.
     */
    private function createZipArchive()
    {
        $zip = new ZipArchive();
        if ($zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->basePath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($this->basePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }
    }
}
?>
