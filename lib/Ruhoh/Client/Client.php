<?php namespace Ruhoh\Client;

use \Ruhoh;
use \Ruhoh\Friend;
use \Ruhoh\Urls;
use \Ruhoh\Utils;
use \Symfony\Component\Yaml\Yaml;
use \Filesystem;

/**
 * Ruhoh client class
 *
 * @package Ruhoh\Client
 * @author Jerome Foray
 **/
class Client
{

    /**
     * Paths to templates
     *
     * @var array
     **/
    protected $paths = [
        'page_template' => '',
        'draft_template' => '',
        'post_template' => '',
        'layout_template' => '',
        'theme_template' => ''
    ];

    /**
     * Clone url for blog scaffold
     *
     * @var string
     **/
    protected $blog_scaffold = 'git://github.com/ruhoh/blog.git';

    /**
     * Constructor
     *
     * @return void
     **/
    public function __construct($data)
    {
        $this->iterator = 0;
        $this->setupPaths();
        $this->setupOptions($data);

        if (isset($data['args'][1]) && $data['args'][1] == 'new') {
            $cmd = 'blog';
        } else {
            $cmd = (isset($data['args'][1]) ? $data['args'][1] : 'help');
        }
        if (!method_exists($this, $cmd)) {
            Friend::say(
                function($f) use($cmd) {
                    $f::red("Command not found : $cmd");
                    exit(1);
                }
            );
        }

        if (!in_array($cmd, ['help', 'blog', 'compile'])) {
            Ruhoh::getInstance()->setup();
        }

        call_user_func([$this, $cmd]);
    }

    /**
     * Setup paths to scaffolds
     *
     * @return void
     **/
    public function setupPaths()
    {
        define('DS', DIRECTORY_SEPARATOR);
        $this->paths['page_template']   = Ruhoh::getRoot() . DS . "scaffolds" . DS .  "page.html";
        $this->paths['draft_template']  = Ruhoh::getRoot() . DS . "scaffolds" . DS .  "draft.html";
        $this->paths['post_template']   = Ruhoh::getRoot() . DS . "scaffolds" . DS .  "post.html";
        $this->paths['layout_template'] = Ruhoh::getRoot() . DS . "scaffolds" . DS .  "layout.html";
        $this->paths['theme_template']  = Ruhoh::getRoot() . DS . "scaffolds" . DS .  "theme";
    }

    /**
     * Setup properties
     *
     * @return void
     **/
    public function setupOptions($data)
    {
        $this->args           = $data['args'];
        $this->options        = $data['options'];
        $this->opt_parser     = $data['opt_parser'];
        $this->options['ext'] = str_replace('.', '', (isset($this->options['ext'])) ?: 'md');
    }

    /**
     * Display help message
     *
     * @return void
     **/
    public function help()
    {
        $file = realpath(__DIR__) . DS . "help.yml";
        $content = Yaml::parse($file);
        $options = $this->opt_parser->help();
        Friend::say(
            function($f) use($content, $options) {
                $f::plain($content['description']);
                $f::plain();
                $f::plain($options);
                $f::plain();
                $f::plain('Commands:');
                $f::plain("\n\n");
                foreach ($content['commands'] as $c) {
                    $f::green('  ' . $c['command']);
                    $f::plain('    ' . $c['desc']);
                }
            }
        );
    }

    /**
     * Create a new draft
     *
     * @return void
     **/
    public function draft()
    {
        $this->draftOrPost('draft');
    }

    /**
     * Create a new post
     *
     * @return void
     **/
    public function post()
    {
        $this->draftOrPost('post');
    }

    /**
     * Create a new draft or a new post
     *
     * @return void
     **/
    public function draftOrPost($type)
    {
        $filename = Ruhoh::getInstance()->paths['posts'];
        while (Filesystem::exists($filename)) {
            $name = isset($this->args[2]) ? $this->args[2] : "untitled-$type";
            if ($this->iterator > 0) {
                $name = "$name-$this->iterator";
            }
            $name = Urls::toSlug($name);
            $filename = Ruhoh::getInstance()->paths['posts'] . DIRECTORY_SEPARATOR . "$name." . $this->options['ext'];
            $this->iterator++;
        }

        Filesystem::mkdir(dirname($filename));

        $output = Filesystem::get($this->paths["${type}_template"]);
        $output = str_replace('{{DATE}}', \Ruhoh\Parsers\Posts::formattedDate(time()), $output);

        Filesystem::put($filename, $output);

        Friend::say(
            function($f) use($type, $filename) {
                $f::green("New $type: " . Utils::relativePath($filename));
                $f::green('View drafts at the Url: /dash');
            }
        );
    }

    /**
     * Create a new page file
     *
     * @return void
     **/
    public function page()
    {
        $name = isset($this->args[2]) ? $this->args[2] : null;
        $test = str_replace(' ', '', $this->args[2]);
        if (is_null($name) || empty($test)) {
            Friend::say(
                function($f) {
                    $f::red('Please specify a path');
                    $f::plain('  ex: ruhoh page projects/hello-world');
                    exit(1);
                }
            );
        }

        $filename = Ruhoh::getInstance()->paths['pages'] . DIRECTORY_SEPARATOR . str_replace(' ', '-', $this->args[2]);
        $ext = Filesystem::extension($filename);
        if (empty($ext)) {
            $filename .= DIRECTORY_SEPARATOR . "index" . $this->options['ext'];
        }
        if (Filesystem::exists($filename)) {
            if ($this->ask("$filename already exists. Do you want to overwrite?", ['y', 'n']) == 'n') {
                echo "Create new page: \e[31maborted!\e[0m";
                exit(1);
            }
        }

        Filesystem::mkdir(dirname($filename));
        $output = Filesystem::get($this->paths["page_template"]);
        Filesystem::put($filename, $output);

        Friend::say(
            function($f) use($filename) {
                $f::green("New page:");
                $f::plain(Utils::relativePath($filename));
            }
        );
    }

    /**
     * Update draft filenames to their corresponding titles.
     *
     * @return void
     * @todo Check for name confilct ?
     **/
    public function titleize()
    {
        foreach (\Ruhoh\Parsers\Posts::files() as $file) {
            if (!preg_match('/^untitled/', pathinfo($file, PATHINFO_BASENAME))) {
                continue;
            }
            $parsed_page = Utils::parsePageFile($file);
            if (!$parsed_page['data']['title']) {
                continue;
            }
            $new_name = Urls::toSlug($parsed_page['data']['title']);
            $new_file = dirname($file) . DIRECTORY_SEPARATOR . $new_name . '.' . Filesystem::extension($file);
            Filesystem::move($file, $new_file);
            Friend::say(
                function($f) use($file, $new_file) {
                    $f::green("Renamed $file to: $new_file");
                }
            );
        }
    }

    /**
     * Compile to static website
     *
     * @return void
     **/
    public function compile()
    {
        \Ruhoh\Program::compile(isset($this->args[2]) ? $this->args[2] : null);
    }

    /**
     * Create a new blog at the directory provided.
     *
     * @return void
     **/
    public function blog()
    {
        $name = isset($this->args[2]) ? $this->args[2] : null;
        if (is_null($name)) {
            Friend::say(
                function($f) {
                    $f::red('Please specify a directory path.');
                    $f::cyan('  ex: ruhoh blog the-blogist');
                    exit(1);
                }
            );
        }

        $target_directory = getcwd() . DIRECTORY_SEPARATOR . $name;

        if (Filesystem::exists($target_directory)) {
            Friend::say(
                function($f) use($target_directory) {
                    $f::red("$target_directory already exists.");
                    $f::plain('  Specify another directory or `rm -rf` this directory first.');
                    exit(1);
                }
            );
        }

        Friend::say(
            function($f) use($target_directory) {
                $f::plain('Trying this command:');
                $f::cyan("  git clone $this->blog_scaffold \"$target_directory\"");

                exec("git clone $this->blog_scaffold \"$target_directory\"", $output, $return_var);
                if ($return_var === 0) {
                    $f::green('Success! Now do...');
                    $f::cyan("  cd $target_directory");
                    $f::cyan('  php -S localhost:9292');
                    $f::cyan('  http://localhost:8080');
                } else {
                    $f::red('Could not git clone blog scaffold. Please try it manually:');
                    $f::cyan("  git clone $this->blog_scaffold \"$target_directory\"");
                    exit(1);
                }
            }
        );
    }

    /**
     * Create a new theme scaffold with the given name.
     *
     * @return void
     **/
    public function theme()
    {
        $name = isset($this->args[2]) ? $this->args[2] : null;
        if (is_null($name)) {
            Friend::say(
                function($f) {
                    $f::red('Please specify a theme name.');
                    $f::cyan('  ex: ruhoh theme the-rain');
                    exit(1);
                }
            );
        }

        $target_directory = Ruhoh::getInstance()->paths['theme'] . DIRECTORY_SEPARATOR . '..';
        $target_directory = $target_directory . DIRECTORY_SEPARATOR . str_replace(' ', '-', $name);

        if (Filesystem::exists($target_directory)) {
            if ($this->ask("$target_directory already exists. Do you want to overwrite?", ['y', 'n']) == 'n') {
                echo "Create new theme: \e[31maborted!\e[0m";
                exit(1);
            } else {
                if (Filesystem::isDirectory($target_directory)) {
                    Filesystem::deleteDirectory($target_directory);
                } else {
                    Filesystem::delete($target_directory);
                }
            }
        }

        Filesystem::copy($this->paths['theme_template'], $target_directory);

        Friend::say(
            function($f) use($target_directory) {
                $f::green('New theme scaffold:');
                $f::green($target_directory);
            }
        );
    }

    /**
     * Create a new layout file for the active theme.
     *
     * @return void
     **/
    public function layout()
    {
        $name = isset($this->args[2]) ? $this->args[2] : null;
        if (is_null($name)) {
            Friend::say(
                function($f) {
                    $f::red('Please specify a layout name.');
                    $f::cyan('  ex: ruhoh layout splash');
                    exit(1);
                }
            );
        }

        $filename = Ruhoh::getInstance()->paths['theme_layouts'] . DIRECTORY_SEPARATOR;
        $filename = $filename . strtolower(str_replace(' ', '-', $this->args[2])) . ".html";
        if (Filesystem::exists($filename)) {
            if ($this->ask("$filename already exists. Do you want to overwrite?", ['y', 'n']) == 'n') {
                echo "Create new layout: \e[31maborted!\e[0m";
                exit(1);
            }
        }

        Filesystem::mkdir(dirname($filename));
        $output = Filesystem::get($this->paths["layout_template"]);
        Filesystem::put($filename, $output);

        Friend::say(
            function($f) use($filename) {
                $f::green("New layout:");
                $f::plain(Utils::relativePath($filename));
            }
        );
    }

    /**
     * List drafts
     *
     * @return void
     **/
    public function drafts()
    {
        $this->listType(__FUNCTION__);
    }

    /**
     * List posts
     *
     * @return void
     **/
    public function posts()
    {
        $this->listType(__FUNCTION__);
    }

    /**
     * List pages
     *
     * @return void
     **/
    public function pages()
    {
        $this->listType(__FUNCTION__);
    }

    /**
     * Return the payload hash for inspection/study
     *
     * @return void
     **/
    public function payload()
    {
        DB::updateAll();
        Friend::say(
            function($f) {
                $f::plain(DB::prettyInspect());
            }
        );
    }

    /**
     * Outputs a list of the given data-type to the terminal.
     *
     * @return string answer
     **/
    protected function listType($type)
    {
        switch ($type) {
            case 'posts':
                DB::update('posts');
                $data = DB::$posts['dictionary'];
                break;
            case 'drafts':
                DB::update('posts');
                $drafts = DB::$posts['drafts'];
                $data = [];
                foreach ($drafts as $id) {
                    $data[$id] = DB::$posts['dictionary'][$id];
                }
                break;
            case 'pages':
                DB::update('pages');
                $data = DB::$pages;
                break;
            default:
                $data = [];
                break;
        }

        if ($this->verbose) {
            Friend::say(
                function($f) use($data) {
                    foreach ($data as $p) {
                        $f::cyan('- ' . $p['id']);
                        $f::plain('  title: ' . $p['title']);
                        $f::plain('  url: ' . $p['url']);
                    }
                }
            );
        } else {
            Friend::say(
                function($f) use($data) {
                    foreach ($data as $p) {
                        $f::cyan('- ' . $p['id']);
                    }
                }
            );
        }
    }

    /**
     * Ask user
     *
     * @return string answer
     **/
    protected function ask($message, $valid_options = [])
    {
        $answer = '';
        if ($valid_options) {
            while (!in_array($answer, $valid_options)) {
                $answer = $this->getStdin("$message [" . implode('/', $valid_options) . "] ");
            }
        } else {
            $answer = $this->getStdin($message);
        }
        return $answer;
    }

    /**
     * Get answer
     *
     * @return string answer
     **/
    protected function getStdin($message)
    {
        echo $message;
        return trim(fgets(STDIN, 4096));
    }
}

