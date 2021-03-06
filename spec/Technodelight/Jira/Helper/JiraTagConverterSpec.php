<?php

namespace spec\Technodelight\Jira\Helper;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Technodelight\Jira\Helper\ColorExtractor;

class JiraTagConverterSpec extends ObjectBehavior
{
    function let()
    {
        $output = new NullOutput();
        $this->beConstructedWith($output, new ColorExtractor);
    }

    function it_does_not_convert_anything()
    {
        $this->convert('some string')->shouldReturn('some string');
    }

    function it_converts_code_block()
    {
        $this->convert('{code}something{code}')->shouldReturn('<comment>something</>');
    }

    function it_converts_bold_and_underscore()
    {
        $this->convert('*bold*')->shouldReturn('<options=bold>bold</>');
        $this->convert('_underscore_')->shouldReturn('<options=underscore>underscore</>');
    }

    function it_converts_mentions()
    {
        $this->convert('[~technodelight]')->shouldReturn('<fg=cyan>technodelight</>');
    }

    function it_converts_panels()
    {
        $panelSource = <<<EOL
{panel}
something{panel}
EOL;
        $panelParsed = <<<EOL

+-----------+
|           |
| something |
|           |
+-----------+

EOL;

        $this->convert($panelSource)->shouldReturn($panelParsed);
    }

    function it_converts_tables()
    {
        $table = <<<EOF
||Attribute Name ||Attribute PIM ID||New values||Values to be removed||
|Chemistry required|724|Processless| |
|Test|234| | |

test
||Attribute Name ||Attribute PIM ID||New values||Values to be removed||
|Chemistry required|724|Processless| |
|Test|234| | |
EOF;

        $bufferedOutput = new BufferedOutput();
        $tableRenderer = new Table($bufferedOutput);
        $tableRenderer
            ->setRows([
                ['Attribute Name', 'Attribute PIM ID', 'New values', 'Values to be removed'],
                new TableSeparator(),
                ['Chemistry required', '724', 'Processless', ' '],
                new TableSeparator(),
                ['Test', '234', ' ', ' ']
            ]);
        $tableRenderer->render();
        $renderedTable = $bufferedOutput->fetch();

        $this->convert($table)->shouldReturn($renderedTable.PHP_EOL.'test'.PHP_EOL.trim($renderedTable));
    }

    function it_merges_definitions()
    {
        $this->convert('*_BOLD UNDERSCORED_* *_BOLD UNDERSCORED_*')
             ->shouldReturn('<options=bold,underscore>BOLD UNDERSCORED</> <options=bold,underscore>BOLD UNDERSCORED</>');
        $this->convert('*_BOLDUNDERSCORE_* _UNDERSCORE_')
             ->shouldReturn('<options=bold,underscore>BOLDUNDERSCORE</> <options=underscore>UNDERSCORE</>');
        $this->convert('*_B_*' . PHP_EOL . '*_B_*')
            ->shouldReturn('<options=bold,underscore>B</>'.PHP_EOL.'<options=bold,underscore>B</>');
    }

}
