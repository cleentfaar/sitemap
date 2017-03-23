# Sitemap

Flexible library for generating XML sitemaps from any source to any filesystem.

For more information about the actual XML protocol, check out 
the [sitemap.org documentation](https://sitemap.org)


### Features

- Supports a lot of filesystems (uses the [Gaufrette abstraction layer](https://knplabs.github.io/Gaufrette/))
- Generates XML files with automatic rotation (preventing too many URLs or filesize limit being reached)
- Automatically lists all generated files into a single 'index' XML (to be [submitted to search engines](https://www.google.com/webmasters/tools/sitemap-list))


### Documentation
1. [Getting started](docs/getting-started.md)
1. [Symfony bridge](docs/symfony-bridge.md)
1. [Contributing](docs/contributing.md) 


### FAQ

#### Should I generate XML sitemaps for my site?

Google mentions the following applicable cases for having sitemap XMLs (see https://support.google.com/webmasters/answer/156184):

- Your site is really large. As a result, it’s more likely Google web crawlers might overlook crawling some of your new or recently updated pages.
- Your site has a large archive of content pages that are isolated or not linked to each other. If you site pages do not naturally reference each other, you can list them in a sitemap to ensure that Google does not overlook some of your pages.
- Your site is new and has few external links to it. Googlebot and other web crawlers crawl the web by following links from one page to another. As a result, Google might not discover your pages if no other sites link to them.
- Your site uses rich media content, is shown in Google News, or uses other sitemaps-compatible annotations. Google can take additional information from sitemaps into account for search, where appropriate.
