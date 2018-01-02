<!--Skygear CDN-->
<script src="https://code.skygear.io/js/polyfill/latest/polyfill.min.js"></script>
<script src="https://code.skygear.io/js/skygear/latest/skygear.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/4.8.1/firebase.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.js"></script>
<script src="js/moment.min.js"></script>

<script src="grade.js"></script>

<title>Admin Panel - Corner Detection Challenge</title>

<div id="app">
  <h2>Admin Panel</h2>
  <div class="row">
    <div class="col s3">
      <h5>Compile Queue</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submit in queue">
            <td>{{submit.name}}</td>
            <td>{{moment(submit.time).format('HH:mm:ss')}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col s4">
      <h5>Compiling</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="compiling && compiling!={}">
            <td>{{compiling.name}}</td>
            <td>{{moment(compiling.time).format('HH:mm:ss')}}</td>
            <td>{{moment(compiling.compile_time).format('HH:mm:ss')}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col s5">
      <h5>Compiled</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
            <th>grade time</th>
            <th>Detail</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(submit,k) in compiled">
            <td>{{submit.name}}</td>
            <td>{{moment(submit.time).format('HH:mm:ss')}}</td>
            <td>{{moment(submit.compile_time).format('HH:mm:ss')}}</td>
            <td>{{moment(submit.grade_time).format('HH:mm:ss')}}</td>
            <td>
              <button class="waves-effect waves-light btn" :onclick="`ViewDetail('${k}')`">Details</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div id="detail" class="modal">
    <div class="modal-content">
      <h3>Detail</h3>
      <table v-if="detail_index">
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
            <th>grade time</th>
            <th>mark</th>
            <th>perfect</th>
            <th>great</th>
            <th>good</th>
            <th>bad</th>
            <th>miss</th>
            <th>extra</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{detail.name}}</td>
            <td>{{moment(detail.time).format('HH:mm:ss')}}</td>
            <td>{{moment(detail.compile_time).format('HH:mm:ss')}}</td>
            <td>{{moment(detail.grade_time).format('HH:mm:ss')}}</td>
            <td>{{detail.grade.mark}}</td>
            <td>{{detail.grade.perfect}}</td>
            <td>{{detail.grade.great}}</td>
            <td>{{detail.grade.good}}</td>
            <td>{{detail.grade.bad}}</td>
            <td>{{detail.grade.miss}}</td>
            <td>{{detail.grade.extra}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
    </div>
  </div>
</div>

<script>

  var users = {}
  var compiling = false

  var app = new Vue({
    el: "#app",
    data: {
      queue: [],
      compiling: false,
      compiled: [],
      detail_index: false
    },
    methods: {
      moment: moment
    },
    computed: {
      detail() {
        if (this.detail_index)
          return this.compiled[this.detail_index]
        return {}
      }
    }
  })

  var ViewDetail = (index) => {
    app.detail_index = index
    $('#detail').modal('open')
  }

  var config = {
    apiKey: "AIzaSyDeXslekRSxKlQzvdS3b908i18s1Ztg5ak",
    authDomain: "corner-ch.firebaseapp.com",
    databaseURL: "https://corner-ch.firebaseio.com",
    projectId: "corner-ch",
    storageBucket: "gs://corner-ch.appspot.com",
    messagingSenderId: "202391887409",

    rules: {
      ".read": true,
      ".write": true
    }
  };
  firebase.initializeApp(config);
  var storage = firebase.storage();
  var storageRef = storage.ref();
  skygear.config({
    'endPoint': 'https://cornerch.skygeario.com/', // trailing slash is required
    'apiKey': '93e92fb17bce4768820d623c71ca7b6d',
  }).then(() => {

    console.log('skygear container is now ready for making API calls.');
    Login()
    FetchAll()
    skygear.pubsub.on('upload', (name) => {
      UploadListener(name)
    });
    skygear.pubsub.on('online', (name) => {
      if (name == "?")
        skygear.pubsub.publish('online', 'Y')
    });
    skygear.pubsub.publish('online', 'Y')
  })

  var UploadListener = (obj) => {
    console.log('recieved upload', obj)
    Push(obj)
    skygear.pubsub.publish(obj.name, "received")
  }

  var Push = (item) => {
    app.queue.push(item)
    if (!compiling) {
      CompileSingle()
    }
  }

  var Login = () => {
    let name = 'Leslie'
    let pw = 'BoyGod'
    console.log('hi')
    skygear.auth.loginWithUsername(name, pw)
      .then((user) => {
        is_login = true
        console.log(user); // user object
      }, (error) => {
        console.error(error);
      })
  }

  var FetchAll = () => {
    ref = firebase.database().ref("users")
    ref.on("value", function (snapshot) {
      console.log(snapshot.val());
      users = snapshot.val()
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
  }

  var FetchUser = (name, callback) => {
    ref = firebase.database().ref("users/" + name)
    ref.once("value").then(function (snapshot) {
      console.log(snapshot.val());
      if (callback) callback(snapshot.val())
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
  }

  var SaveUser = (name, obj) => {
    firebase.database().ref('users/' + name).set(obj)
  }

  var CompileSingle = () => {
    if(app.queue.length==0)return
    var { name: name, time: time } = app.queue.shift()
    compiling = true
    app.compiling = { name: name, time: time, compile_time: moment() }
    console.log('compiling',app.compiling)
    console.log(`start compile ${name}`)
    storageRef.child(`files/${name}/${btoa(time)}.cpp`).getDownloadURL().then(function (url) {
      console.log('cpp url', url)
      $.ajax({
        url: `sandbox/compile.php?name=${name}&url=${btoa(url)}`,
        success: (data) => {
          console.log('compile done', data)
          app.compiling.grade_time = moment()
          if ('error' in JSON.parse(data)) {
            console.log('error', JSON.parse(data))
            skygear.pubsub.publish(name, { type: 'grade', time: time, error: JSON.parse(data).error })
            app.compiling.error = JSON.parse(data).error
            let temp = {}
            Object.assign(temp,app.compiling)
            app.compiled.splice(0, 0, temp)
          }
          else {
            grade = Grade(JSON.parse(data))
            FetchUser(name, (user) => {
              if (user.mark && user.mark > grade.mark) return
              user.mark = grade.mark
              user.grade = grade
              user.grade_time = time
              SaveUser(name, user)
            })
            skygear.pubsub.publish(name, { type: 'grade', time: time, grade: grade })
            app.compiling.grade = grade
            let temp = {}
            //Object.assign(temp,app.compiling)
            for (let prop in app.compiling) {
              temp[prop] = app.compiling[prop]
            }
            app.compiled.splice(0, 0, temp)
          }
          app.compiling=false
          if (app.queue.length > 0) {
            CompileSingle()
          }
          else {
            compiling = false
          }
        }
      })
    }).catch(function (error) {
      // Handle any errors
      console.log(error)
      skygear.pubsub.publish(data.name, { type: 'grade', time: time, error: 'network fail' })
      if (app.queue.length > 0) {
        CompileSingle()
      }
      else {
        compiling = false
      }
    });
  }
  $('.modal').modal()
</script>
