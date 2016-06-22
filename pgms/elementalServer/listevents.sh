#!/bin/bash
ELEMENTAL_SERVER_IP='201.31.12.7'
curl -H "Accept: application/xml" http://${ELEMENTAL_SERVER_IP}/api/live_events
