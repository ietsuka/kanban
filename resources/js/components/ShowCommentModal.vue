<template>
  <div>
    <form class="mt-8 flex justify-between" @submit.prevent="handleAddNewComment(taskId)">
      <input class="block w-full px-2 py-1 text-lg border-b border-blue-800 rounded" type="text" placeholder="Enter a comment" v-model.trim="newComment.comment"/>
      <button type="submit" class="px-3 py-1 fs-6 text-white bg-orange-600 hover:bg-orange-500 rounded">
        追加
      </button>
    </form>
    <div v-show="errorMessage">
      <span class="text-xs text-red-500">
        {{ errorMessage }}
      </span>
    </div>
  <transition-group>
      <div v-for="taskComment in comments" :key="taskComment.id" class="mb-5 p-3 h-5">
        <div class="flex justify-between">
          <p class="block mb-2">
            {{ taskComment.comment }}
          </p>
          <p class="mt-4 text-gray-500">
            {{ formatDate(taskComment.created_at) }}
          </p>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import moment from "moment"

export default {
  props: {
    taskId: Number
  },
  data() {
    return {
      comments: [],
      newComment: {
        comment: "",
        task_id: null
      },
      errorMessage: ""
    };
  },
  mounted() {
    axios
    .get("/comments/")
    .then(res => {
      for (let i = 0; i < res.data.length; i++) {
        if(res.data[i].task_id == this.taskId) {
          this.comments.push(res.data[i])
        }
      }
    })
    .catch(err => {
      console.log(err);
    });
  },
  methods: {
    formatDate(date){
      if( !!date ) return moment(date).format( "MM/DD" )
    },
    handleAddNewComment(taskId) {
      if (!this.newComment.comment) {
        this.errorMessage = "The comment field is required";
        return;
      }
      this.newComment.task_id = taskId
      axios
      .post("/comments", this.newComment)
      .then(res => {
        this.takeComment()
        this.newComment.comment = ""
      })
      .catch(err => {
        this.handleErrors(err);
      });
    },
    takeComment() {
      this.comments = []
      axios
      .get("/comments/")
      .then(res => {
        for (let i = 0; i < res.data.length; i++) {
          if(res.data[i].task_id == this.taskId) {
            this.comments.push(res.data[i])
          }
        }
      })
      .catch(err => {
        console.log(err);
      });
    }
  }
};
</script>