#!/usr/bin/perl
use strict;
use warnings;

use lib 'src/Api/Perl';
use RateLimiter;

my $clientIp = $ARGV[0];

my $obj = RateLimiter->new(
    server => '127.0.0.1:6379',
    maxrate => 100,
    maxrate_period => 60,
    namespace => 'rate_limit'
);

if ($obj->rate_limited($clientIp)) {
    print "1";
} else {
    print "0";
}