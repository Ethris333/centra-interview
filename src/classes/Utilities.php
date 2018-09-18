<?php
namespace KanbanBoard;

class Utilities
{
    const CONFIG_PATH = "../classes/config/github.ini";

	public static function env(string $name, $default = null) : string
    {
		$value = getenv($name);

        if (!empty($value)) {
            return $value;
        }

		if ($default !== null) {
			return $default;
		}

		return die('Environment variable ' . $name . ' not found or has no value');
	}

	public static function hasValue(array $array, $key) : bool
    {
		return is_array($array) && array_key_exists($key, $array) && !empty($array[$key]);
	}

	public static function dump($data) : void
    {
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
	}

	public static function setEnvironmentVariables() : void
    {
        $config = parse_ini_file(self::CONFIG_PATH);

        putenv("GH_CLIENT_ID=" . $config['gh_client_id']);
        putenv("GH_CLIENT_SECRET=" . $config['gh_client_secret']);
        putenv("GH_ACCOUNT=" . $config['gh_account']);
        putenv("GH_REPOSITORIES=" . $config['gh_repositories']);
    }
}