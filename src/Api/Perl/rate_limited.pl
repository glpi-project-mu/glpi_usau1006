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
    maxrate => 10,
    maxrate_period => 60,
    namespace => 'rate_limit'
);

if ($obj->rate_limited($clientIp)) {
    print "1";
} else {
    print "0";
}