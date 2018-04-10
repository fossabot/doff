<?php

/*
 * This file is the array-organize package.
 *
 * (c) Simon Micheneau <contact@simon-micheneau.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimonDevelop;

use Symfony\Component\Yaml\Yaml;
use SimonDevelop\ArrayOrganize;

/**
 * Class Doff
 * Manage your data yaml with query functions and more.
 */
class Doff
{
    /**
     * @var string $path absolut path of data
     */
    private $path;

    /**
     * @param array $settings Settings
     */
    public function __construct(array $settings = [])
    {
        if (!empty($settings)) {
            if (isset($settings["path"])) {
                if (file_exists($settings["path"])) {
                    if (is_dir($settings["path"])) {
                        if (is_readable($settings["path"]) && is_writable($settings["path"])) {
                            if (substr($settings["path"], -1) == "/") {
                                $this->path = $settings["path"];
                            } else {
                                $this->path = $settings["path"]."/";
                            }
                        } else {
                            throw new \Exception("Unable build:
                            Path setting of data must be accessible reading and writing");
                        }
                    } else {
                        throw new \Exception("Unable build: Path setting of data must be a dir");
                    }
                } else {
                    throw new \Exception("Unable build: Path setting of data does not exist");
                }
            } else {
                throw new \Exception("Unable build: Argument $settings need 'path' param for absolut path of data");
            }
        } else {
            throw new \Exception("Unable build: Argument $settings must not be empty");
        }
    }

    /**
     * @param string $dataName name of data file
     * @return array|bool return array or false for error
     */
    public function getData(string $dataName)
    {
        $filename = strtolower($dataName);
        if (file_exists($this->path.$filename.".yml")) {
            $value = Yaml::parseFile($this->path.$filename.".yml");
            if ($value === null) {
                return [];
            } elseif (is_array($value)) {
                return $value;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string $dataName name of data file
     * @param array $filter filter param
     * @return array|bool return array or false for error
     */
    public function select(string $dataName, array $filter)
    {
        $filename = strtolower($dataName);
        if (file_exists($this->path.$filename.".yml")) {
            $value = Yaml::parseFile($this->path.$filename.".yml");
            if ($value === null) {
                return [];
            } elseif ($value != null && is_array($value)) {
                $datas = new ArrayOrganize($value);
                $result = $datas->dataFilter($filter);
                if ($result == true) {
                    return $datas->getData();
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string $dataName name of data file
     * @param array $update update param
     * @param array $where where param
     * @return array|bool return array or false for error
     */
    public function update(string $dataName, array $update, array $where = [])
    {
        $filename = strtolower($dataName);
        if (file_exists($this->path.$filename.".yml")) {
            $value = Yaml::parseFile($this->path.$filename.".yml");
            if ($value != null && is_array($value)) {
                $datas = new ArrayOrganize($value);
                $result = $datas->dataFilter($where);
                if ($result == true) {
                    $value2 = $datas->getData();

                    foreach ($value as $k1 => $v1) {
                        foreach ($value2 as $v2) {
                            if ($v1 === $v2) {
                                foreach ($v1 as $k => $v) {
                                    if (array_key_exists($k, $update)) {
                                        $value[$k1][$k] = $update[$k];
                                    }
                                }
                            }
                        }
                    }

                    $yaml = Yaml::dump($value);
                    file_put_contents($this->path.$filename.".yml", $yaml);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @return string Absolut path of data
     */
    public function getPath()
    {
        return $this->path;
    }
}
