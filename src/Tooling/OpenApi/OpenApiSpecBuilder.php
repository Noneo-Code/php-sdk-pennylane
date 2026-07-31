<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

final class OpenApiSpecBuilder
{
    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    public function build(string $llmsTxtUrl): OpenApiSpecBuildResult
    {
        $llmsTxt = $this->fetcher->get($llmsTxtUrl);
        $referenceUrls = LlmsIndexParser::extractReferenceUrls($llmsTxt);

        $merger = new OpenApiSpecMerger();
        $skippedUrls = [];

        foreach ($referenceUrls as $url) {
            $page = $this->fetcher->get($url);
            $fragment = ReferenceFragmentExtractor::extract($page);

            if (null === $fragment) {
                $skippedUrls[] = $url;
                continue;
            }

            $merger->addFragment($fragment);
        }

        return new OpenApiSpecBuildResult($merger->toArray(), $skippedUrls);
    }
}
