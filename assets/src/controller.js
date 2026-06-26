import { Controller } from '@hotwired/stimulus';

const getTokenizedLocation = (token) => {
  const url = new URL(window.location);
  url.searchParams.set('token', token);
  return url.toString();
}

export default class extends Controller {
  solved(e) {
    const token = e.detail.token;
    const url = getTokenizedLocation(token);
    Turbo.visit(url)
  }
}
