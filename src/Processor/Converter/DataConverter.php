<?php

declare(strict_types=1);

namespace App\Processor\Converter;

use App\YamlReader;

/**
 * Prepare the data to be sent to the API, using registered dedicated converters
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataConverter implements DataConverterInterface
{
    /** @var DataConverterInterface[] */
    private $converters;

    /** @var YamlReader */
    private $yamlReader;

    public function __construct(YamlReader $yamlReader)
    {
        $this->yamlReader = $yamlReader;
        $dataConvertersConfig = $this->yamlReader->parseFile('config/data_converters.yml');
        $dataConverterClasses = $dataConvertersConfig['data_converters'];

        foreach ($dataConverterClasses as $dataConverterClass) {
            $this->converters[] = new $dataConverterClass();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $attribute, string $data)
    {
        /** @var DataConverterInterface $converter */
        foreach ($this->converters as $converter) {
            if ($converter->support($attribute)) {
                return $converter->convert($attribute, $data);
            }
        }

        throw new \RuntimeException(sprintf(
            'No converter found to convert data "%s" for attribute "%s"',
            $data,
            json_encode($attribute)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function support(array $attribute): bool
    {
        /** @var DataConverterInterface $converter */
        foreach ($this->converters as $converter) {
            if ($converter->support($attribute)) {
                return true;
            }
        }

        return false;
    }
}
