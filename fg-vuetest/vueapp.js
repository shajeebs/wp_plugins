( function() {
  var vm = new Vue({
    el: document.querySelector('#mount'),
    template:`<div><h4>My Latest Posts</h4>
                    <div>
                        <p v-for="post in posts">
                            <a v-bind:href="post.link">{{post.title.rendered}}</span></a>
                        </p>
                    </div>
                </div>`,
    data: {
        posts: []
    },
    methods:{
        fetchPosts: function() {
            //var url = '/wp-json/wp/v2/posts?filter[orderby]=date';
            var url = '/wordpressv1/wp-json/wp/v2/posts?filter[orderby]=date';
            fetch(url).then((response) => {
                //console.log(response);
                return response.json()
            }).then((data)=>{
                this.posts = data;
                //console.log(this.posts);
            });
        }
    },
    mounted: function(){
      console.log("Component is mounted");
      this.fetchPosts();
    }
  });
})();