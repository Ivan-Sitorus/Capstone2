import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '1m', target: 30 }, // ramp-up
    { duration: '3m', target: 30 }, // steady state
    { duration: '1m', target: 0  }, // ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000', 'p(99)<3000'],
    http_req_failed: ['rate<0.01'],
  },
};

export default function () {
  const res = http.get(
    'https://pos-cafe-prototype-main.test/customer/menu?table=1',
    { headers: { 'Accept': 'text/html' } }
  );

  check(res, {
    'status 200': (r) => r.status === 200,
    'response < 2000ms': (r) => r.timings.duration < 2000,
  });

  sleep(1);
}
