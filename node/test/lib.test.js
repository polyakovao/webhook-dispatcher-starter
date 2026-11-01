import test from 'node:test';
import assert from 'node:assert/strict';
import { backoffSeconds, sign } from '../src/lib.js';

test('first retry waits the base delay when jitter factor is 1.0 (rand=0.5)', () => {
  assert.equal(backoffSeconds(1, 0.5), 60);
});

test('delay doubles with every attempt', () => {
  assert.equal(backoffSeconds(2, 0.5), 120);
  assert.equal(backoffSeconds(3, 0.5), 240);
  assert.equal(backoffSeconds(4, 0.5), 480);
});

test('delay is capped at 6 hours before jitter is applied', () => {
  assert.equal(backoffSeconds(30, 0.5), 21600);
});

test('jitter spans 50%..150% of the delay', () => {
  assert.equal(backoffSeconds(1, 0), 30);
  const high = backoffSeconds(1, 0.999999);
  assert.ok(high > 89 && high < 90, `expected just under 90, got ${high}`);
});

test('sign produces the known HMAC-SHA256 hex vector', () => {
  const body = '{"id":"evt_1","type":"order.created"}';
  assert.equal(
    sign('whsec_test', '1700000000', body),
    'b4299661e5387b192e6a17c961991dce11e3adb10aabc368d96f3b7a148387e3',
  );
});
