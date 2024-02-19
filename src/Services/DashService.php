<?php

namespace Goldfinch\Dashcore\Services;

use Carbon\Carbon;
use Composer\InstalledVersions;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\Versioned\Versioned;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Manifest\VersionProvider;
use SilverStripe\Security\InheritedPermissions;

class DashService
{
    /**
     * Gets the size of public/assets folder
     */

    public static function getAssetsSize(): string
    {
        $f = BASE_PATH . '/public/assets/';
        // $io = popen('/usr/bin/du -sh' . $f, 'r');
        // $assetsSize = fgets($io, 4096);
        // $assetsSize = substr($assetsSize, 0, strpos($assetsSize, '\t'));
        // pclose($io);

        $assetsSize = 0;
        foreach (
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($f))
            as $file
        ) {
            $assetsSize += $file->getSize();
        }

        return round($assetsSize / 1000000, 2);
    }

    /**
     * Gets the size of vendor folder
     */

    public static function getVendorSize(): string
    {
        $f = BASE_PATH . '/vendor/';

        $assetsSize = 0;
        foreach (
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($f))
            as $file
        ) {
            try {
                $assetsSize += $file->getSize();
            } catch (\RuntimeException $ex) {
                //
            }
        }

        return round($assetsSize / 1000000, 2);
    }

    /**
     * Gets list of installed composer packages
     */
    public static function getComposerInstalledPackageList(): array
    {
        $list = [];

        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $list[] = [
                'name' => $package,
                'version' => InstalledVersions::getVersion($package),
            ];
        }

        return $list;
    }

    /**
     * Gets some useful server data
     */
    public static function getServerData(): array
    {
        $data = [
            'dns' => @dns_get_record(Director::host()),

            'disk_total' => round(disk_total_space('/') / 1000000 / 1000, 2), // gb
            'timezone' => date_default_timezone_get(),
            'server_ip' => $_SERVER['SERVER_ADDR'],
            'server_email' => $_SERVER['SERVER_ADMIN'],
            'cdn' => isset($_SERVER['HTTP_CDN_LOOP'])
                ? $_SERVER['HTTP_CDN_LOOP']
                : null, // cloudflare
            'server_ipcountry' => isset($_SERVER['HTTP_CF_IPCOUNTRY'])
                ? $_SERVER['HTTP_CF_IPCOUNTRY']
                : null,
            'php' => phpversion(),
            'allocated_php_memory' => round(memory_get_usage() / 1000000, 2), // mb
            'mysql' => mysqli_get_client_info(), // if used?
        ];

        return $data;
    }

    /**
     * Gets SilverStripe version
     */
    public static function getSilverStripeVersion(): array
    {
        $versionProvider = new VersionProvider();

        return [
            'version' => $versionProvider->getVersion(),
            'modules' => $versionProvider->getModules(),
        ];
    }

    /**
     * Gets latest git commits
     */
    public static function getGitCommits($limit = 10): array|null
    {
        $path = BASE_PATH . '/.git/';

        if (file_exists($path)) {
            chdir(BASE_PATH);
            exec(
                'git log -' .
                    $limit .
                    ' --pretty=\'%H|-|-|%s|-|-|%an|-|-|%ae|-|-|%aD\'',
                $logs,
            );

            $list = [];

            foreach ($logs as $log) {
                $commit = explode('|-|-|', $log);

                $list[] = [
                    'hash' => $commit[0],
                    'hashShort' => substr($commit[0], 0, 7),
                    'commit' => $commit[1],
                    'author' => $commit[2],
                    'email' => $commit[3],
                    'date' => $commit[4],
                    'dateFull' => Carbon::parse($commit[4])->format('l, F jS Y, H:i'),
                    'dateNow' => Carbon::parse($commit[4])->diffForHumans(),
                ];
            }

            return $list;
        }

        return null;
    }

    /**
     * Gets all branches
     */
    public static function getGitBranches(): array|null
    {
        $path = BASE_PATH . '/.git/';

        if (file_exists($path)) {
            chdir(BASE_PATH);
            exec('git branch', $branchs);

            $list = [];

            foreach ($branchs as $key => $b) {
                $list[] = [
                    'main' => $b[0] == '*',
                    'name' => last(explode(' ', $b)),
                ];
            }

            return $list;
        }

        return null;
    }

    /**
     * Gets main branch
     */
    public static function getGitCurrentBranch(): string|null
    {
        $branches = self::getGitBranches();

        if ($branches) {
            $return = null;

            foreach ($branches as $branch) {
                if ($branch['main']) {
                    $return = $branch['name'];
                    break;
                }
            }

            return $return;
        }

        return null;
    }

    /**
     * Gets all kinds of data for the initial dashpanel setup
     */
    public static function getPanelInitialData()
    {
        if (Controller::has_curr()) {
            $object = Controller::curr();

            $cfg = SiteConfig::current_site_config();

            $user = Security::getCurrentUser();

            // Session info
            // $member->LoginSessions()->first();
            // $currentSessions = $member->LoginSessions()->filterAny([
            //     'Persistent' => 1,
            //     'LastAccessed:GreaterThan' => date('Y-m-d H:i:s', $maxAge)
            // ]);
            // getSchemaDataDefaults

            // MFA
            // $user->getDefaultRegisteredMethodName();
            // $user->getDefaultRegisteredMethod();

            // check for inherit
            if ($object->CanViewType === InheritedPermissions::INHERIT) {
                if ($object->ParentID) {
                    $CanViewType = $object->Parent()->CanViewType;
                } else {
                    $CanViewType = $object->getSiteConfig()->CanViewType;
                }
            } else {
                $CanViewType = $object->CanViewType;
            }

            $firstVersion = Versioned::get_version(
                $object->ClassName,
                $object->ID,
                1,
            );

            // ? added here because it cause errors on request of to missing assets (like fonts, woff woff2)
            if (!$firstVersion) {
                return null;
            }

            $lastVersion = Versioned::get_latest_version(
                $object->ClassName,
                $object->ID,
            );

            return [
                'env' => Environment::getEnv('SS_ENVIRONMENT_TYPE'),
                'siteAccess' => $cfg->CanViewType,

                'page' => [
                    'versions' => [
                        'first' => [
                            'version_id' => $firstVersion->Version,
                            'updatedAt' => $firstVersion->LastEdited,
                            'author' => $firstVersion->Author()
                                ? [
                                    'name' => $firstVersion
                                        ->Author()
                                        ->getName(),
                                    'link' =>
                                        '/admin/security/users/EditForm/field/users/item/' .
                                        $firstVersion->Author()->ID .
                                        '/edit',
                                ]
                                : null,
                        ],
                        'last' => [
                            'version_id' => $lastVersion->Version,
                            'updatedAt' => $lastVersion->LastEdited,
                            'author' => $lastVersion->Author()
                                ? [
                                    'name' => $lastVersion->Author()->getName(),
                                    'link' =>
                                        '/admin/security/users/EditForm/field/users/item/' .
                                        $lastVersion->Author()->ID .
                                        '/edit',
                                ]
                                : null,
                        ],
                    ],
                    'icon' =>
                        substr($object->getIconClass(), 0, 3) === 'bi-'
                            ? substr($object->getIconClass(), 3)
                            : $object->getIconClass(),
                    'classNamespace' => $object->ClassName,
                    'className' => last(explode('\\', $object->ClassName)),
                    'canViewType' => $CanViewType,
                    'createdAt' => $object->Created,
                    'updatedAt' => $object->LastEdited,
                    'isOnDraft' => $object->isOnDraft(),
                    'isPublished' => $object->isPublished(),
                    'stagesDiffer' => $object->stagesDiffer(),
                    'canPublish' => $object->canPublish(),
                    'canUnpublish' => $object->canUnpublish(),
                    'canEdit' => $object->canEdit(),
                ],

                'user' => [
                    'email' => $user->email,
                    'firstname' => $user->FirstName,
                    'surname' => $user->Surname,
                    'groups' => $user
                        ->Groups()
                        ->map('ID')
                        ->toArray(),
                ],
            ];
        }

        return null;
    }
}
