<?php

namespace Solspace\Addons\FreeformNext\Library\Helpers;

use Exception;
use Solspace\Addons\FreeformNext\Controllers\FieldController;
use Solspace\Addons\FreeformNext\Controllers\FormController;
use Solspace\Addons\FreeformNext\Controllers\StatusController;
use Solspace\Addons\FreeformNext\Model\FieldModel;
use Solspace\Addons\FreeformNext\Model\FormModel;
use Solspace\Addons\FreeformNext\Repositories\FieldRepository;
use Solspace\Addons\FreeformNext\Repositories\FormRepository;
use Solspace\Addons\FreeformNext\Utilities\ControlPanel\Navigation\NavigationLink;

class FreeformHelper
{
    public static function getVersion(): string
    {
        if (file_exists(PATH_THIRD . '/freeform_next/Library/Pro')) {
            return FREEFORM_PRO;
        }

        if (file_exists(PATH_THIRD . '/freeform_next/ft.freeform_next.php')) {
            return FREEFORM_LITE;
        }

        return FREEFORM_EXPRESS;
    }

    public static function validate(FormModel|FieldModel $item): void
    {
        if (self::getVersion() !== FREEFORM_EXPRESS) {
            return;
        }

        if ($item instanceof FormModel) {
            $count = (int) ee()->db
                ->select('COUNT(*) as total')
                ->get('freeform_next_forms')
                ->row()
                ->total;

            if (!$item->id && $count > 0) {
                throw new Exception('Form limit reached');
            }

            return;
        }

        if ($item instanceof FieldModel) {
            $count = (int) ee()->db
                ->select('COUNT(*) as total')
                ->get('freeform_next_fields')
                ->row()
                ->total;

            if (!$item->id && $count >= 15) {
                throw new Exception('Maximum limit of 15 fields reached.');
            }
        }
    }

    public static function isExpressEdition(): bool
    {
        return self::getVersion() === FREEFORM_EXPRESS;
    }

    public static function getRightLinks(FormController|FieldController|StatusController $controller): array
    {
        $version = self::getVersion();
        $link = '';
        $title = '';
        $showLink = false;

        if ($controller instanceof FormController) {
            $count = count(FormRepository::getInstance()->getAllForms());

            $showLink = $version !== FREEFORM_EXPRESS || $count === 0;
            $link = 'forms/new';
            $title = 'New Form';
        } elseif ($controller instanceof FieldController) {
            $count = count(FieldRepository::getInstance()->getAllFields());

            $showLink = $version !== FREEFORM_EXPRESS || $count < 15;
            $link = 'fields/new';
            $title = 'New Field';
        } elseif ($controller instanceof StatusController) {
            $showLink = $version !== FREEFORM_EXPRESS;
            $link = 'settings/statuses/new';
            $title = 'New Status';
        }

        if (!$showLink) {
            return [];
        }

        return [
            [
                'title' => lang($title),
                'link' => UrlHelper::getLink($link),
            ],
        ];
    }

    public static function getEditionName(): string
    {
        $version = self::getVersion();

        if ($version === FREEFORM_PRO) {
            return 'Freeform';
        }

        if ($version === FREEFORM_LITE) {
            return 'Freeform Lite';
        }

        return 'Freeform Express';
    }

    public static function getColumns(array $columns): array
    {
        if (self::getVersion() !== FREEFORM_EXPRESS) {
            return $columns;
        }

        return array_slice($columns, 0, count($columns) - 2, true);
    }

    public static function getColumnCount(array $columns): array
    {
        if (self::getVersion() !== FREEFORM_EXPRESS) {
            return $columns;
        }

        $newColumns = [];

        foreach ($columns as $column) {
            $data = array_slice($column, 0, count($column) - 2, true);
            $data[1]['content'] = strip_tags($data[1]['content'], '<span>');

            $newColumns[] = $data;
        }

        return $newColumns;
    }

    public static function getNavigation(NavigationLink $item): void
    {
        $version = self::getVersion();

        $link = '';
        $showLink = false;

        if ($item->getMethod() === 'fields') {
            $count = count(FieldRepository::getInstance()->getAllFields());

            $link = 'fields/new';
            $showLink = $version !== FREEFORM_EXPRESS || $count < 15;
        } elseif ($item->getMethod() === 'forms') {
            $count = count(FormRepository::getInstance()->getAllForms());

            $link = 'forms/new';
            $showLink = $version !== FREEFORM_EXPRESS || $count < 1;
        }

        if ($showLink) {
            $item->setButtonLink(new NavigationLink('New', $link));
        }
    }

    public static function isFreeformAtLeast(string $minVersion): bool
    {
        $addon = ee('Addon')->get('freeform_next');

        $installed = $addon->getInstalledVersion();
        if (!$installed) {
            return false;
        }

        return version_compare($installed, $minVersion, '>=');
    }
}
