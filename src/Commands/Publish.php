<?php namespace CodeigniterExt\Queue\Commands;

use Config\Autoload;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;

class Publish extends BaseCommand
{
    
    protected $group        = 'Queue';
    protected $name         = 'queue:publish';
    protected $description  = 'Publish queue migration into the current application folder.';
    protected $usage        = 'queue:publish';
    protected $arguments    = [];
	protected $options 		= [];
    protected $sourcePath;

    //--------------------------------------------------------------------

    /**
     * Displays the help for the spark cli script itself.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->determineSourcePath();

        // Config
        if (CLI::prompt('Publish Config file?', ['y', 'n']) == 'y')
        {
            $this->publishConfig();
        }
        
        // Migration
        if (CLI::prompt('Publish queue migration?', ['y', 'n']) == 'y')
        {
            $this->publishMigration();
        }


    }

    protected function publishMigration()
    {
        $map = directory_map($this->sourcePath . '/Database/Migrations');
        foreach ($map as $file)
        {
            $content = file_get_contents("{$this->sourcePath}/Database/Migrations/{$file}");
            $this->writeFile("Database/Migrations/{$file}", $content);
        }
        CLI::write('Remember to run `spark migrate:latest` to migrate the database.', 'blue');
    }

    protected function publishConfig()
    {
        $path = "{$this->sourcePath}/Config/Queue.php";
        $content = file_get_contents($path);
        $appNamespace = APP_NAMESPACE;
        $content = str_replace('namespace CodeigniterExt\Queue\Config', "namespace {$appNamespace}\Config", $content);
        $this->writeFile("Config/Queue.php", $content);
    }

    /**
     * Replaces the Myth\Auth namespace in the published
     * file with the applications current namespace.
     *
     * @param string $contents
     * @param string $originalNamespace
     * @param string $newNamespace
     *
     * @return string
     */
    protected function replaceNamespace(string $contents, string $originalNamespace, string $newNamespace): string
    {
        $appNamespace = APP_NAMESPACE;
        $originalNamespace = "namespace {$originalNamespace}";
        $newNamespace = "namespace {$appNamespace}\\{$newNamespace}";
        return str_replace($originalNamespace, $newNamespace, $contents);
    }

    /**
     * Determines the current source path from which all other files are located.
     */
    protected function determineSourcePath()
    {
        $this->sourcePath = realpath(__DIR__ . '/../');

        if ($this->sourcePath == '/' || empty($this->sourcePath))
        {
            CLI::error('Unable to determine the correct source directory. Bailing.');
            exit();
        }
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @param string $path
     * @param string $content
     */
    protected function writeFile(string $path, string $content)
    {
        $config = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];

        $directory = dirname($appPath . $path);

        if (! is_dir($directory))
        {
            mkdir($directory);
        }

        try
        {
            write_file($appPath . $path, $content);
        }
        catch (\Exception $e)
        {
            $this->showError($e);
            exit();
        }

        $path = str_replace($appPath, '', $path);

        CLI::write(CLI::color('  created: ', 'green') . $path);
    }
}
