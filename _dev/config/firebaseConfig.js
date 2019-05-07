import firebase from 'firebase/app';
import 'firebase/auth';

const config = {
  apiKey: 'AIzaSyDm_ot2sXXQqotOLXkcQlVZt419loUJY5s',
  authDomain: 'prestashop-97d42.firebaseapp.com',
  databaseURL: 'https://prestashop-97d42.firebaseio.com',
  projectId: 'prestashop-97d42',
  storageBucket: 'prestashop-97d42.appspot.com',
  messagingSenderId: '622428354639',
};

firebase.initializeApp(config);
