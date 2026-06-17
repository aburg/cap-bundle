<?php

namespace AndreasBurg\CapBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cap
{
  public function __construct(
    private ?string $siteKey = null,
    private ?string $siteSecret = null,
  ) {
    //
  }

  public function getSiteKey(): ?string
  {
    return $this->siteKey;
  }

  public function getSiteSecret(): ?string
  {
    return $this->siteSecret;
  }
}
