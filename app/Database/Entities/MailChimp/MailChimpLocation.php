<?php

declare(strict_types=1);

namespace App\Database\Entities\MailChimp;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 * @todo Maybe string type should have been used here depending on business task
 */
class MailChimpLocation
{
    /**
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     * @var float
     */
    private $latitude;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     * @var float
     */
    private $longitude;


    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }


    public function toArray(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }
}
