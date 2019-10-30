#!/usr/bin/env bash
ips=($(hostname -I))
index=0
for ip in "${ips[@]}"
  do
    php occ config:system:set trusted_proxies $index --value=$ip
    ((index++))
  done
exit 0