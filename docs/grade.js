/**
 * grade.js
 * 
 * Corner Dectection Grader
 * Author: Dipsy
 * 
 * Created on 2017-12-31
 */

answers = false

$.ajax({
  url:'ans/ans.json',
  success:data=>{
    answers = data
  }
}).catch(function (error) {
  console.log(error)
})

Grade = (data)=>{
  results = Parse(data.result)
  console.log('parsed result',results)
  marks = 0
  results.forEach(photo=>{
    edges = Pair(photo.corners,answers[photo.name])
    photo.edges = edges
    marks += edges.reduce((value,edge)=>{return value+GradePair(edge[0],edge[1])},0)
  })
  console.log(marks,results)
  skygear.pubsub.publish(data.name, marks)
}

/**
 * Parse the result: given a string from result.txt
 * <image name1> <corner count> <corner1 x> <corner1 y> \n
 * <image name2> <corner count> <corner1 x> <corner1 y> \n...
 * 
 * return:
 * [
 * {
 * name:<path to corresponding image file>
 * length:<length of corner list>
 * corners:[[<x>,<y>],[<x>,<y>]]
 * }
 * ]
 * 
 */
Parse = (data)=>{
  photo_results = []
  photo_strings = data.split(/\n/g).filter(s=>{return s!=''})
  photo_strings.forEach(photo_string => {
    photo_content = photo_string.split(' ')
    photo_result = {
      name:photo_content[0],
      length:Number(photo_content[1]),
      corners:photo_content.splice(2,photo_content[1]*2)
    }
    photo_result.corners=photo_result.corners.filter((c,k)=>{return k%2===0}).map((c,k)=>{return [Number(c),Number(photo_result.corners[k*2+1])]})
    photo_results.push(photo_result)
  });
  return photo_results
}

/**
 * pair two sets of points to maximize grade
 * using stable marriage, which is programmer's view on marriage lol
 * 
 * return edges connecting the point to paired point
 * no edges means no matching edge
 */
Pair = (res_,ans_)=>{
  res= res_.slice()
  ans= ans_.slice()
  res.forEach((r,i)=>{r.index=i})
  ans.forEach((r,i)=>{r.index=i})
  res.forEach((A,a_i)=>{
    A.mark_list=[]
    ans.forEach((B,b_i)=>{
      A.mark_list.push({point:B,mark:Dist(A,B)})
    })
    A.mark_list.sort((a,b)=>{return a.mark-b.mark}) //small to large
  })
  ans.forEach((A,a_i)=>{
    A.mark_list=[]
    res.forEach((B,b_i)=>{
      A.mark_list.push({point:B,mark:Dist(A,B)})
    })
    A.mark_list.sort((a,b)=>{return a.mark-b.mark}) //small to large
  })
  A_single = ans.slice()
  B_single = res.slice()
  A_forever_single = []
  edges = []
  while(A_single.length!=0){
    //A perpose to his most desire:
    //if B's current husband is less desire than A, take over the position that B's husband become single
    //else perpose to next most desire
    //if no more desire, A forever single
    A=A_single.shift()
    for(i = 0; i<A.mark_list.length; i++){
      B = A.mark_list[i].point
      if(!B.link){
        //B is available
        edges.push([B,A])
        B.link=A
        A.link=B
        B_single.splice(B_single.indexOf(B))
        break
      }else if(B.mark_list.findIndex(o=>o.point==A)<B.mark_list.findIndex(o=>o.point==B.link)){
        edges.splice(edges.indexOf([B,B.link]))
        edges.push([B,A])
        A_single.push(B.link)
        delete B.link.link
        B.link=A
        A.link=B
        B_single.splice(B_single.indexOf(B))
        break
      }
    }
    if(!A.link){
      A_forever_single.push(A)
    }
  }
  return edges.map(edge=>{return edge.map(pt=>{return [pt[0],pt[1]]})})
}


/**
 * grade the point with answer point
 */
GradePair = (res_pt,ans_pt)=>{
  dist = Dist(res_pt,ans_pt)
  if(dist<4)return 10         //perfect
  else if(dist<9)return 6     //great
  else if(dist<16)return 3    //good
  else return 1               //bad
}

Dist = (A,B) =>{
  let dx = A[0]-B[0]
  let dy = A[1]-B[1]
  return dx*dx+dy*dy
}