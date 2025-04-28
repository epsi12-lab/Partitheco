// assets/js/base.js

var variable = '';
const constante = '';
let variableScoped = '';

let string = '';
let number = 0;
let boolean = true;

let array = ['JohnDoe', 'JaneDoe'];
let object = {
    username: '',
};

const user = {
    username: 'JohnDoe',
};
const user2 = user;
user2.username = 'JaneDoe';

console.log(user.username);

const user3 = { ...user };
const array2 = [...array];

console.log(add(1, 2));

function add(nb1, nb2) {
    return nb1 + nb2;
}

const add2 = function(nb1, nb2) {
    return nb1 + nb2;
};

const add3 = (nb1, nb2) => nb1 + nb2;

const newUser = {
    username: 'username',
    firstName: 'John',
    lastName: 'Doe',
    address: {
        path: 'Main St',
        number: 123,
        city: 'Springfield',
        zipcode: '12345',
    },
};
const { lastName, firstName, username: uname } = newUser;
console.log(lastName, firstName, uname);

const newUser2 = { username: 'username2', firstName: 'Jane', lastName: 'Doe', address: null };
const newUsers = [newUser, newUser2];
const addressNumbers = newUsers.map(user => user.address?.number ?? -1);
console.log(addressNumbers);

document.querySelector('footer')?.addEventListener('click', () => {
    console.log('footer cliqué');
});

console.log('après');

document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.project-item');
    const obs   = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });
  
    items.forEach(item => {
      item.style.animationPlayState = 'paused';
      obs.observe(item);
    });

    const detail = document.querySelector('.project-detail');
    if (detail) {
      void detail.offsetWidth;
    }
});
  
  
