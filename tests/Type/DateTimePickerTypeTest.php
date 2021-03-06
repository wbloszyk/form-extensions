<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Form\Tests\Type;

use PHPUnit\Framework\MockObject\MockObject;
use Sonata\Form\Date\MomentFormatConverter;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class DateTimePickerTypeTest extends TypeTestCase
{
    public function testParentIsDateTimeType(): void
    {
        $form = new DateTimePickerType(
            $this->createMock(MomentFormatConverter::class),
            $this->getTranslatorMock(),
            $this->getRequestStack()
        );

        $this->assertSame(DateTimeType::class, $form->getParent());
    }

    public function testGetName(): void
    {
        $type = new DateTimePickerType(
            new MomentFormatConverter(),
            $this->getTranslatorMock(),
            $this->getRequestStack()
        );

        $this->assertSame('sonata_type_datetime_picker', $type->getBlockPrefix());
    }

    public function testSubmitUnmatchingDateFormat(): void
    {
        \Locale::setDefault('en');
        $form = $this->factory->create(DateTimePickerType::class, new \DateTime('2018-06-03 20:02:03'), [
            'format' => \IntlDateFormatter::NONE,
            'dp_pick_date' => false,
            'dp_use_seconds' => false,
            'html5' => false,
        ]);

        $form->submit('05:23');
        $this->assertFalse($form->isSynchronized());
    }

    public function testSubmitMatchingDateFormat(): void
    {
        \Locale::setDefault('en');
        $form = $this->factory->create(DateTimePickerType::class, new \DateTime('2018-06-03 20:02:03'), [
            'format' => \IntlDateFormatter::NONE,
            'dp_pick_date' => false,
            'dp_use_seconds' => false,
            'html5' => false,
        ]);

        $this->assertSame('8:02 PM', $form->getViewData());

        $form->submit('5:23 AM');
        $this->assertSame('1970-01-01 05:23:00', $form->getData()->format('Y-m-d H:i:s'));
        $this->assertTrue($form->isSynchronized());
    }

    protected function getExtensions()
    {
        $type = new DateTimePickerType(
            new MomentFormatConverter(),
            $this->getTranslatorMock(),
            $this->getRequestStack()
        );

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    /**
     * @return MockObject|TranslatorInterface|LegacyTranslatorInterface\
     */
    private function getTranslatorMock(): MockObject
    {
        if (interface_exists(TranslatorInterface::class)) {
            return $this->createMock(TranslatorInterface::class);
        }

        $translator = $this->createMock(LegacyTranslatorInterface::class);
        $translator->method('getLocale')->willReturn('en');

        return $translator;
    }

    private function getRequestStack(): RequestStack
    {
        $requestStack = new RequestStack();
        $request = $this->createMock(Request::class);
        $request
            ->method('getLocale')
            ->willReturn('en');
        $requestStack->push($request);

        return $requestStack;
    }
}
