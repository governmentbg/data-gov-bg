<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ConversionTest extends TestCase
{
    public function testXmlToJson()
    {
        $this->post(url('api/xml2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => file_get_contents(storage_path('tests/sample.xml')),
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->post(url('api/xml2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => file_get_contents(storage_path('tests/sample-excel.xml')),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testJsonToXml()
    {
        $this->post(url('api/json2xml'), [
            'api_key'   => $this->getApiKey(),
            'data'      => json_decode('{"note": {"to": "Tove", "body": "Dont forget me this weekend!"}}', true),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testCsvToJson()
    {
        $this->post(url('api/csv2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => '
                album, year, US_peak_chart_post
                The White Stripes, 1999, -
                De Stijl, 2000, -
                White Blood Cells, 2001, 61
                Elephant, 2003, 6
                Get Behind Me Satan, 2005, 3
                Icky Thump, 2007, 2
                Under Great White Northern Lights, 2010, 11
                Live in Mississippi, 2011, -
                Live at the Gold Dollar, 2012, -
                Nine Miles from the White City, 2013, -
            ',
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testJsonToCsv()
    {
        $this->post(url('api/json2csv'), [
            'api_key'   => $this->getApiKey(),
            'data'      => json_decode('{"note": {"to": "Tove", "body": "Dont forget me this weekend!"}}', true),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testKmlToJson()
    {
        $this->post(url('api/kml2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => '
                <kml xmlns="http://www.opengis.net/kml/2.2">
                    <Placemark>
                        <name>A simple placemark on the ground</name>
                        <Point>
                            <coordinates>8.542952335953721,47.36685263064198,0</coordinates>
                        </Point>
                    </Placemark>
                </kml>
            ',
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testJsonToKml()
    {
        $this->post(url('api/json2kml'), [
            'api_key'   => $this->getApiKey(),
            'data'      => json_decode('{
                "type": "MultiPoint",
                "coordinates": [
                    [
                        null,
                        null
                    ],
                    [
                        52.654207356793,
                        71.472447836566
                    ]
                ]
            }', true),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testRdfToJson()
    {
        $this->post(url('api/rdf2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => '
                <rdf:RDF
                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                xmlns:cd="http://www.recshop.fake/cd#">

                <rdf:Description
                rdf:about="http://www.recshop.fake/cd/Empire Burlesque">
                <cd:artist>Bob Dylan</cd:artist>
                <cd:country>USA</cd:country>
                <cd:company>Columbia</cd:company>
                <cd:price>10.90</cd:price>
                <cd:year>1985</cd:year>
                </rdf:Description>

                </rdf:RDF>
            ',
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testJsonToRdf()
    {
        $this->post(url('api/json2rdf'), [
            'api_key'   => $this->getApiKey(),
            'data'      => json_decode('{
                "http://www.recshop.fake/cd/Empire Burlesque": {
                    "http://www.recshop.fake/cd#artist": [
                        {
                            "type": "literal",
                            "value": "Bob Dylan"
                        }
                    ],
                    "http://www.recshop.fake/cd#country": [
                        {
                            "type": "literal",
                            "value": "USA"
                        }
                    ],
                    "http://www.recshop.fake/cd#company": [
                        {
                            "type": "literal",
                            "value": "Columbia"
                        }
                    ],
                    "http://www.recshop.fake/cd#price": [
                        {
                            "type": "literal",
                            "value": "10.90"
                        }
                    ],
                    "http://www.recshop.fake/cd#year": [
                        {
                            "type": "literal",
                            "value": "1985"
                        }
                    ]
                }
            }', true),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testImgToJson()
    {
        $this->post(url('api/img2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.jpg'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testPdfToJson()
    {
        $this->post(url('api/pdf2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.pdf'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testDocToJson()
    {
        $this->post(url('api/doc2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.doc'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testDocxToJson()
    {
        $this->post(url('api/doc2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.docx'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testXlsToJson()
    {
        $this->post(url('api/xls2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.xls'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testOdsToJson()
    {
        $this->post(url('api/ods2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.ods'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testSlkToJson()
    {
        $this->post(url('api/slk2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.slk'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testTsvToJson()
    {
        $this->post(url('api/tsv2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.tsv'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testXsdToJson()
    {
        $this->post(url('api/xsd2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.xsd'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testRtfToJson()
    {
        $this->post(url('api/rtf2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.rtf'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testOdtToJson()
    {
        $this->post(url('api/odt2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.odt'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }


    public function testXlsxToJson()
    {
        $this->post(url('api/xls2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.xlsx'))),
        ])->assertStatus(200)->assertJson(['success' => true]);
    }
}
