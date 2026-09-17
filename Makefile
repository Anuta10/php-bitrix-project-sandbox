.PHONY: serve test check lint

serve:
	php -S 127.0.0.1:8080 -t public public/router.php

test:
	php tests/run.php

check:
	php bin/check.php

lint:
	find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
