<?php

declare(strict_types=1);

namespace App\Database\Entities\MailChimp;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use EoneoPay\Utils\Str;
use RuntimeException;

/**
 * @ORM\Entity
 */
class MailChimpListMember extends MailChimpEntity
{
    public const DATETIME_FORMAT = DateTimeImmutable::ATOM;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(name="mail_chimp_id", type="string", nullable=true)
     * @var string
     */
    private $mailChimpId;

    /**
     * @ORM\Column(name="email_address", type="string")
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(name="unique_email_id", type="string", nullable=true)
     * @var string
     */
    private $uniqueEmailId;

    /**
     * @ORM\Column(name="email_type", type="string", nullable=true)
     * @var string|null
     */
    private $emailType;

    /**
     * @ORM\Column(name="status", type="string", nullable=true)
     * @var string|null
     */
    private  $status;

    /**
     * @ORM\Column(name="unsubscribe_reason", type="string", nullable=true)
     * @var string|null
     */
    private  $unsubscribeReason;

    /**
     * @ORM\Column(name="ip_signup", type="string", nullable=true)
     * @var string|null
     */
    private $ipSignup;

    /**
     * @ORM\Column(name="timestamp_signup", type="datetime_immutable")
     * @var DateTimeImmutable
     */
    private $timestampSignup;

    /**
     * @ORM\Column(name="ip_opt", type="string", nullable=true)
     * @var string|null
     */
    private $ipOpt;

    /**
     * @ORM\Column(name="timestamp_opt", type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    private $timestampOpt;

    /**
     * @ORM\Column(name="member_rating", type="integer", nullable=true)
     * @var int|null
     */
    private $memberRating;

    /**
     * @ORM\Column(name="last_changed", type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    private $lastChanged;

    /**
     * @ORM\Column(name="language", type="string", nullable=true)
     * @var string|null
     */
    private $language;

    /**
     * @ORM\Column(name="vip", type="string")
     * @var bool
     */
    private $vip = false;

    /**
     * @ORM\Column(name="email_client", type="string", nullable=true)
     * @var string|null
     */
    private $emailClient;

    /**
     * @Embedded(class = "MailChimpLocation")
     * @var MailChimpLocation
     */
    private $location;

    /**
     * @ORM\Column(name="web_id", type="integer", nullable=true)
     * @var int|null
     */
    private $web_id;

    /**
     * @ORM\Column(name="marketing_permissions", type="array")
     * @var array
     */
    private $marketingPermissions;

    /**
     * @ORM\Column(name="source", type="string", nullable=true)
     * @var string|null
     */
    private $source;

    /**
     * @ORM\Column(name="tags_count", type="integer")
     * @var int
     */
    private $tagsCount = 0;


    // @todo Skipped as overhead for test task. Implementation is the same as location field
    private $mergeFields;
    private $interests;
    private $lastNote;
    private $tags;


    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        if (isset($data['location'])) {
            $this->location =  new MailChimpLocation(
                (float) $data['location']['latitude'],
                (float) $data['location']['longitude']
            );
        }

        $this->setTimestampSignup($data['timestamp_signup'] ?? new DateTimeImmutable());

        if ($data['timestamp_opt']) {
            $this->setTimestampOpt($data['timestamp_opt']);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getMailChimpId(): string
    {
        return $this->mailChimpId;
    }

    public function setMailChimpId(string $mailChimpId): self
    {
        $this->mailChimpId = $mailChimpId;
        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getUniqueEmailId(): string
    {
        return $this->uniqueEmailId;
    }

    public function setUniqueEmailId(string $uniqueEmailId): self
    {
        $this->uniqueEmailId = $uniqueEmailId;
        return $this;
    }

    public function getEmailType(): ?string
    {
        return $this->emailType;
    }

    public function setEmailType(?string $emailType): self
    {
        $this->emailType = $emailType;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUnsubscribeReason(): ?string
    {
        return $this->unsubscribeReason;
    }

    public function setUnsubscribeReason(?string $unsubscribeReason): self
    {
        $this->unsubscribeReason = $unsubscribeReason;
        return $this;
    }

    public function getIpSignup(): ?string
    {
        return $this->ipSignup;
    }

    public function setIpSignup(?string $ipSignup): self
    {
        $this->ipSignup = $ipSignup;
        return $this;
    }

    public function getTimestampSignup(): DateTimeImmutable
    {
        return $this->timestampSignup;
    }

    /**
     * I would make argument strongly typed, but usage of ->fill() method
     * requires 'string' values in specific format
     *
     * @var DateTimeImmutable|string $timestampSignup
     * @return self
     */
    public function setTimestampSignup($timestampSignup): self
    {
        if ($timestampSignup instanceof DateTimeImmutable) {
            $this->timestampSignup = $timestampSignup;
        } else {
            $this->timestampSignup = new DateTimeImmutable($timestampSignup);
        }

        return $this;
    }

    public function getIpOpt(): ?string
    {
        return $this->ipOpt;
    }

    public function setIpOpt(?string $ipOpt): self
    {
        $this->ipOpt = $ipOpt;
        return $this;
    }

    public function getTimestampOpt(): ?DateTimeImmutable
    {
        return $this->timestampOpt;
    }

    public function setTimestampOpt($timestampOpt): self
    {
        if ($timestampOpt instanceof DateTimeImmutable) {
            $this->timestampOpt = $timestampOpt;
        } else {
            $this->timestampOpt = new DateTimeImmutable($timestampOpt);
        }

        return $this;
    }

    public function getMemberRating(): ?int
    {
        return $this->memberRating;
    }

    public function setMemberRating(?int $memberRating): self
    {
        $this->memberRating = $memberRating;
        return $this;
    }

    public function getLastChanged(): ?string
    {
        return $this->lastChanged;
    }

    public function setLastChanged(?string $lastChanged): self
    {
        $this->lastChanged = $lastChanged;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function vip(): bool
    {
        return $this->vip;
    }

    public function setVip(bool $vip): self
    {
        $this->vip = $vip;
        return $this;
    }

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(?string $emailClient): self
    {
        $this->emailClient = $emailClient;
        return $this;
    }

    public function getLocation(): MailChimpLocation
    {
        return $this->location;
    }

    // Can't use typing here because of ->fill() method
    public function setLocation($location): self
    {
        if ($location instanceof MailChimpLocation) {
            $this->location = $location;
        } elseif (is_array($location)) {
            $this->location = new MailChimpLocation((float) $location['latitude'], (float) $location['longitude']);
        } else {
            throw new RuntimeException("MailChimpListMember::setLocation(): Invalid location type");
        }

        return $this;
    }

    public function getWebId(): ?int
    {
        return $this->web_id;
    }

    public function setWebId(?int $web_id): self
    {
        $this->web_id = $web_id;
        return $this;
    }

    public function getMarketingPermissions(): array
    {
        return $this->marketingPermissions;
    }

    public function setMarketingPermissions(array $marketingPermissions): self
    {
        $this->marketingPermissions = $marketingPermissions;
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getTagsCount(): int
    {
        return $this->tagsCount;
    }

    public function setTagsCount(int $tagsCount): self
    {
        $this->tagsCount = $tagsCount;
        return $this;
    }


    public function getValidationRules(): array
    {
        return [
            'email_address' => 'required|string|email:rfc,dns',
            'unique_email_id' => 'string',
            'email_type' => 'string|in:html,text',
            'status' => 'required|string|in:subscribed,unsubscribed,cleaned,pending,transactional,archived',
            'unsubscribe_reason' => 'string',
            'ip_signup' => 'string',
            'timestamp_signup' => 'string',
            'ip_opt' => 'string',
            'timestamp_opt' => 'string',
            'member_rating' => 'numeric',
            'last_changed' => 'string',
            'language' => 'string',
            'vip' => 'boolean',
            'email_client' => 'string',
            'location' => 'array',
            'location.latitude' => 'numeric|min:-90|max:90',
            'location.longitude' => 'numeric|min:-180|max:180',
            'web_id' => 'numeric',
            'marketing_permissions' => 'array',
            'source' => 'string',
            'tags_count' => 'numeric',
            // @todo Skipped as overhead for test task. Same as location
//            'merge_fields' => 'array',
//            'interests' => 'array',
//            'last_note' => '',
//            'tags' => '',
        ];
    }

    public function toArray(): array
    {
        $array = [];
        $str = new Str();

        foreach (get_object_vars($this) as $property => $value) {
            $array[$str->snake($property)] = $value;
        }

        if ($this->location) {
            $array['location'] = $this->location->toArray();
        }

        $array['timestamp_signup'] = $this->getTimestampSignup()->format(self::DATETIME_FORMAT);

        if ($this->getTimestampOpt()) {
            $array['timestamp_opt'] = $this->getTimestampOpt()->format(self::DATETIME_FORMAT);
        }

        return $array;
    }
}
