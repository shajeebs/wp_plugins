( function() {
  var vm = new Vue({
    el: document.querySelector('#mount'),
    template:`<div><h4>Student List</h4>
                    <div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Email</th>
                                    <th>Place</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in students">
                                    <td>{{s.id}}</td>
                                    <td>{{s.name}}</td>
                                    <td>{{s.age}}</td>
                                    <td>{{s.email}}</td>
                                    <td>{{s.place}}</td>
                                    <td>{{s.created_at}}</td> 
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`,
    data: {
        students: []
    },
    methods:{
        fetchStudents: function() {
            var url = '/wordpressv1/wp-json/fgvuetest/v1/students?filter[orderby]=date';
            fetch(url).then((response) => {
                //console.log(response);
                return response.json()
            }).then((data)=>{
                this.students = data;
                //console.log(this.posts);
            });
        }
    },
    mounted: function(){
      console.log("Component is mounted");
      this.fetchStudents();
    }
  });
})();

// "<tr><td><input type='hidden' name='productIds[]' value='"+ datafilter.id +"' /></td><td>" + datafilter.name + "</td><td>" + datafilter.cost_price + "</td><td>" + datafilter.sale_price + "</td><td><input type='number' name='quantities[]' value='1' /></td><td>" + datafilter.expiry_date + "</td><td><a href='#' alt='Delete Row' class='deleterow'>X</a></td></tr>";
