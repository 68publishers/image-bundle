<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic;

use Nette;
use Ramsey;
use SixtyEightPublishers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_IMAGE_CREATED", columns={"created"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class Image implements SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="uuid", unique=true)
	 *
	 * @var \Ramsey\Uuid\UuidInterface
	 */
	private $id;

	/**
	 * @ORM\Column(type="datetime")
	 *
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * @ORM\Column(type="datetime")
	 *
	 * @var \DateTime
	 */
	protected $updated;

	/**
	 * @ORM\Column(type="image_info")
	 *
	 * @var \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo
	 */
	protected $source;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $info
	 * @param \Ramsey\Uuid\UuidInterface|NULL                                     $uuid
	 *
	 * @throws \Exception
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $info, ?Ramsey\Uuid\UuidInterface $uuid = NULL)
	{
		$this->id = $uuid ?? Ramsey\Uuid\Uuid::uuid1();
		$this->created = new \DateTime('now', new \DateTimeZone('UTC'));
		$this->updated = new \DateTime('now', new \DateTimeZone('UTC'));

		$this->setSource($info);
		$info->setVersion($this->createSourceVersion($this->created));
	}

	/**
	 * @internal
	 * @ORM\PreUpdate
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function onPreUpdate(): void
	{
		$this->update();
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $info
	 *
	 * @return void
	 */
	public function setSource(SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $info): void
	{
		$this->source = $info;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdated(): \DateTime
	{
		return $this->updated;
	}

	/**
	 * @param \DateTime $dateTime
	 *
	 * @return string
	 */
	private function createSourceVersion(\DateTime $dateTime): string
	{
		$version = $dateTime->format('HyidsM') . Nette\Utils\Random::generate(4, 'a-zA-Z0-9');

		return rtrim(strtr(base64_encode($version), '+/', '-_'), '=');
	}

	/**
	 * {@inheritdoc}
	 */

	/***************** interface \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage *****************/

	/**
	 * {@inheritdoc}
	 */
	public function getId(): Ramsey\Uuid\UuidInterface
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSource(): SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo
	{
		return $this->source;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreated(): \DateTime
	{
		return $this->created;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function update(): void
	{
		$this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
		$this->setSource($source = clone $this->getSource());
		$source->setVersion($this->createSourceVersion($this->updated));
	}
}
