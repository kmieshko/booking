<?php

namespace vendor\core;

class Router
{
    /**
     * Таблица маршрутов
     * @var array
     */
    protected static $routes = [];

    /**
     * Текущий маршрут
     * @var array
     */
    protected static $route = [];

    /**
     * add
     * Добавляет маршрут в таблицу маршрутов
     * @param  string $regex регулярное выражение маршрута
     * @param  array $route маршрут ([controller, action, params])
     *
     * @return void
     */
    public static function add($regex, $route = [])
    {
        self::$routes[$regex] = $route;
    }

    /**
     * getRoutes
     * Возвращает таблицу маршрутов
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * getRoute
     * Возвращает текущий маршрут (controller, action, [params])
     * @return array
     */
    public static function getRoute()
    {
        return self::$route;
    }

    /**
     * matchRoute
     * Ищет URL в таблице маршрутов
     * @param  string $url входящий URL
     * @return boolean
     */
    public static function matchRoute($url)
    {
        foreach (self::$routes as $pattern => $route) {
            if (preg_match("/$pattern/i", $url, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $route[$key] = $value;
                    }
                }
                if (!isset($route['action'])) {
                    $route['action'] = 'index';
                }
                $route['controller'] = self::upperCamelCase($route['controller']);
                self::$route = $route;
                return true;
            }
        }
        return false;
    }

    /**
     * dispatch
     * Перенаправляет URL по корректному маршруту
     * @param  string $url входящий URL
     * @return void
     */
    public static function dispatch($url)
    {
        $url = self::removeQueryString($url);
        if (self::matchRoute($url)) {
            $controller = 'app\controllers\\' . self::$route['controller'] . 'Controller';
            if (class_exists($controller)) {
                $cObj = new $controller(self::$route);
                $action = self::lowerCamelCase(self::$route['action']) . 'Action';
                if (method_exists($cObj, $action)) {
                    $cObj->$action();
                    $cObj->getView();
                }
                else {
                    echo "Method <b>$controller::$action</b> not found";
                }
            }
            else {
                echo "Controller <b>$controller</b> not found";
            }
        }
        else {
            http_response_code(404);
            include '404.html';
        }
    }

    /**
     * upperCamelCase
     * Преобразует имена к виду CamelCase
     * @param  string $name строка для перобразования
     * @return string
     */
    protected static function upperCamelCase($name)
    {
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

    /**
     * lowerCamelCase
     * Преобразует имена к виду camelCase
     * @param  string $name строка для перобразования
     * @return string
     */
    protected static function lowerCamelCase($name)
    {
        $name = self::upperCamelCase($name);
        $name = lcfirst($name);
        return $name;
    }

    /**
     * removeQueryString
     * Возвращает строку без GET параметров
     * @param  string $url запрос URL
     * @return string
     */
    protected static function removeQueryString($url)
    {
        if ($url) {
            $params = explode('&', $url, 2);
            if (false == strpos($params[0], '=')) {
                return rtrim($params[0], '/');
            } else {
                return '';
            }
        }
        return ($url);
    }
}