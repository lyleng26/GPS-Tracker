<?php declare(strict_types=1);

namespace App\Services\Protocol\H02;

use App\Services\Protocol\H02\Parser\Command as CommandParser;
use App\Services\Protocol\H02\Parser\Location as LocationParser;
use App\Services\Protocol\H02\Parser\Sms as SmsParser;
use App\Services\Protocol\ProtocolAbstract;
use App\Services\Protocol\Resource\ResourceAbstract;
use App\Services\Server\Socket\Server;

class Manager extends ProtocolAbstract
{
    /**
     * @const array
     */
    protected const PARSERS = [
        LocationParser::class,
        SmsParser::class,
        CommandParser::class,
    ];

    /**
     * @return string
     */
    public function code(): string
    {
        return 'h02';
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'H02';
    }

    /**
     * @param int $port
     *
     * @return \App\Services\Server\Socket\Server
     */
    public function server(int $port): Server
    {
        return Server::new($port)
            ->socketType('stream')
            ->socketProtocol('ip');
    }

    /**
     * @param string $body
     * @param array $data = []
     *
     * @return array
     */
    public function resources(string $body, array $data = []): array
    {
        return array_filter(array_map(fn ($body) => $this->resource($body, $data), $this->bodies($body)));
    }

    /**
     * @param string $body
     *
     * @return array
     */
    protected function bodies(string $body): array
    {
        preg_match_all('/\*[^#]+#/', $body, $matches);

        return $matches[0];
    }

    /**
     * @param string $body
     * @param array $data = []
     *
     * @return ?\App\Services\Protocol\Resource\ResourceAbstract
     */
    public function resource(string $body, array $data = []): ?ResourceAbstract
    {
        foreach (static::PARSERS as $parser) {
            if (($resource = $parser::new($body, $data)->resource()) && $resource->isValid()) {
                return $resource;
            }
        }

        return null;
    }
}
