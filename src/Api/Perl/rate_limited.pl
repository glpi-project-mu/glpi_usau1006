#!/usr/bin/perl
use strict;
use warnings;

#use lib 'src/Api/Perl';
use FindBin;
use lib "$FindBin::Bin/local/lib/perl5";
use RateLimiter;

my $clientIp = $ARGV[0];

my $obj = RateLimiter->new(
    server => '127.0.0.1:6379',
    password => '0J1vYNum/6yOwX9oLPRn0hQ6hXZotu3ccWfimajBmZNs0FECj5KBHjy5ya7BBiZtogIguA2ej28D4aP4',
    maxrate => 10,
    maxrate_period => 60,
    namespace => 'rate_limit',
    rate_limit_enabled => 1
);

if ($obj->rate_limited($clientIp)) {
    print "1";
} else {
    print "0";
}