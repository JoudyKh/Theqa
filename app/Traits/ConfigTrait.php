<?php
namespace App\Traits;

trait ConfigTrait
{
    protected $config = null;

    /**
     * Initialize the configuration and apply camelCase transformation.
     */
    public function initConfig()
    {
        $this->config = getConfig();

    }



    /**
     * Get the configuration for a specific layer.
     *
     * @param string $layer The layer identifier.
     * @return mixed Configuration array for the layer.
     */
    public function getLayerConfig($layer, $isSingle = false)
    {
        $layerConfig = $this->config['features']['sections']['layers'][$layer] ?? null;
        if ($layerConfig) {
            $withRelationsConfig = $layerConfig['with']['relations']['common'];
            $withFunctionsConfig = $layerConfig['with']['functions']['common'];
            if (app('admin')) {
                $withRelationsConfig = array_merge($withRelationsConfig, $layerConfig['with']['relations']['admin']);
                $withFunctionsConfig = array_merge($withFunctionsConfig, $layerConfig['with']['functions']['admin']);
            }
            if (app('student')) {
                $withRelationsConfig = array_merge($withRelationsConfig, $layerConfig['with']['relations']['student']);
                $withFunctionsConfig = array_merge($withFunctionsConfig, $layerConfig['with']['functions']['student']);
            }

            foreach ($withRelationsConfig as $key => &$relationConfig) {
                $hasQuestionMark = substr($relationConfig, -1) === '?';
                $hasDash = str_contains($relationConfig, '-');
                $hasAsterisk = str_contains($relationConfig, '*');
                if ($isSingle && !$hasDash) {
                    unset($withRelationsConfig[$key]);
                    continue;
                }

                if (!$isSingle && !$hasAsterisk) {
                    unset($withRelationsConfig[$key]);
                    continue;
                }

                if ($hasDash) {
                    $relationConfig = str_replace('-', '', $relationConfig);
                }

                if ($hasAsterisk) {
                    $relationConfig = str_replace('*', '', $relationConfig);
                }

                if ($hasQuestionMark) {
                    $relationConfig = rtrim($relationConfig, '?');
                    if (!request()->has($relationConfig)) {
                        unset($withRelationsConfig[$key]);
                        continue;
                    }
                }
            }

            foreach ($withFunctionsConfig as $key => &$relationConfig) {
                $hasQuestionMark = substr($relationConfig, -1) === '?';
                $hasDash = str_contains($relationConfig, '-');
                $hasAsterisk = str_contains($relationConfig, '*');

                if ($hasQuestionMark) {
                    $relationConfig = rtrim($relationConfig, '?');
                    if (!request()->has($relationConfig)) {
                        unset($withFunctionsConfig[$key]);
                        continue;
                    }
                }

                if ($isSingle && !$hasDash) {
                    unset($withFunctionsConfig[$key]);
                    continue;
                }

                if (!$isSingle && !$hasAsterisk) {
                    unset($withFunctionsConfig[$key]);
                    continue;
                }

                if ($hasDash) {
                    $relationConfig = str_replace('-', '', $relationConfig);
                }

                if ($hasAsterisk) {
                    $relationConfig = str_replace('*', '', $relationConfig);
                }
            }
            $layerConfig['with']['relations'] = [];
            foreach ($withRelationsConfig as $relation) {
                if (strpos($relation, '?') !== false) {
                    $layerConfig['with']['conditionalRelations'][] = $relation;
                } else {
                    $layerConfig['with']['relations'][] = $relation;
                }
            }
            $layerConfig['with']['functions'] = [];
            foreach ($withFunctionsConfig as $function) {
                if (strpos($function, 'scope') !== false) {
                    $layerConfig['with']['scopeFunctions'][] = lcfirst(str_replace('scope', '', $function));
                } else {
                    $layerConfig['with']['functions'][] = $function;
                }
            }

            return $layerConfig;
        }
        return null;
    }
}
