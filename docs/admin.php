<!--Skygear CDN-->
<script src="https://code.skygear.io/js/polyfill/latest/polyfill.min.js"></script>
<script src="https://code.skygear.io/js/skygear/latest/skygear.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/4.8.1/firebase.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.min.js"></script>
<script src="js/moment.min.js"></script>

<script src="grade.js"></script>

<title>Admin Panel - Corner Detection Challenge</title>

<script>

  var users = {}
  var compiling = false
  var queue = []

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
      if(name=="?")
      skygear.pubsub.publish('online','Y')
    });
    skygear.pubsub.publish('online','Y')
  })

  var UploadListener = (obj) => {
    console.log('recieved upload', obj)
    Push(obj)
    skygear.pubsub.publish(obj.name, "received")
  }
  
  var Push = (item)=>{
    queue.push(item)
    if(!compiling){
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

  var FetchUser = (name,callback)=>{
    ref = firebase.database().ref("users/"+name)
    ref.once("value").then(function (snapshot) {
      console.log(snapshot.val());
      if(callback)callback(snapshot.val())
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
  }

  var SaveUser = (name,obj)=>{
    firebase.database().ref('users/' + name).set(obj)
  }

  var CompileSingle = () => {
    var {name:name,time:time} = queue.shift()
    compiling = true
    console.log(`start compile ${name}`)
    storageRef.child(`files/${name}/${btoa(time)}.cpp`).getDownloadURL().then(function (url) {
      console.log('cpp url',url)
      $.ajax({
        url: `sandbox/compile.php?name=${name}&url=${btoa(url)}`,
        success: (data) => {
          console.log('compile done', data)
          if(queue.length>0){
            CompileSingle()
          }
          else{
            compiling = false
          }
          if('error' in JSON.parse(data)){
            console.log('error',JSON.parse(data))
            skygear.pubsub.publish(name, {type:'grade',time:time,error:JSON.parse(data).error})
          }
          else{
            grade = Grade(JSON.parse(data))
            FetchUser(name,(user)=>{
              if(user.mark&&user.mark>grade.mark)return
              user.mark=grade.mark
              user.grade=grade
              user.grade_time = time
              SaveUser(name,user)
            })
            skygear.pubsub.publish(name, {type:'grade',time:time,grade:grade})
          }
        }
      })
    }).catch(function (error) {
      // Handle any errors
      console.log(error)
      skygear.pubsub.publish(data.name, {type:'grade',time:time,error:'network fail'})
      if(queue.length>0){
        CompileSingle()
      }
      else{
        compiling = false
      }
    });
  }
</script>