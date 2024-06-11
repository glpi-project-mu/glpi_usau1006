package RateLimiter;
use strict;
use warnings;
use Redis;
use Time::HiRes qw(time);
use Carp;

sub new {
    my ($class, %params) = @_;
    my $self = {
        redis => Redis->new(
            server => $params{server} || '127.0.0.1:6379',
            password => $params{password}
        ),
        maxrate => $params{maxrate} || 10,
        maxrate_period => $params{maxrate_period} || 60,
        namespace => $params{namespace} || 'rate_limit',
        rate_limit_enabled => $params{rate_limit_enabled},
    };
    bless $self, $class;
    return $self;
}

sub set_config {
    my ($self, %params) = @_;
    $self->{$_} = $params{$_} for keys %params;
}

sub get_config {
    my ($self, $key) = @_;
    return $self->{$key};
}

sub rate_limited {
    my ($self, $clientIp) = @_;

    croak "Client IP is required" unless $clientIp;

    return 0 unless $self->{rate_limit_enabled};

    my $maxrate = $self->{maxrate};
    my $maxrate_period = $self->{maxrate_period};
    my $namespace = $self->{namespace};

    my $now = time;

    # Obtener la lista de intentos desde Redis
    my $tries = $self->{redis}->lrange("$namespace:$clientIp", 0, -1);

    # Reiniciar el contador de intentos si ha pasado el periodo máximo
    if (@$tries && $tries->[0] < $now - $maxrate_period) {
        $self->{redis}->del("$namespace:$clientIp");
        $tries = [];
    }

    # Limpiar intentos antiguos
    while (@$tries && $tries->[0] < $now - $maxrate_period) {
        $self->{redis}->lpop("$namespace:$clientIp");
        shift @$tries;
    }

    # Añadir el nuevo intento si no está limitado y en el mismo segundo
    if (@$tries < $maxrate || ($tries->[-1] && $tries->[-1] < $now)) {
        $self->{redis}->rpush("$namespace:$clientIp", $now);
        $self->{redis}->expire("$namespace:$clientIp", $maxrate_period);
    }

    if (@$tries >= $maxrate) {
        my $limit_log = $self->{redis}->get("$namespace:log:$clientIp") || 0;
        # También limitar el logging en alta carga
        if ($limit_log < $now - 10) {
            warn "Request rate limitation applied for remote $clientIp";
            $self->{redis}->set("$namespace:log:$clientIp", $now);
            $self->{redis}->expire("$namespace:log:$clientIp", 10);
        }
        return 1;
    }

    return 0;
}

1;