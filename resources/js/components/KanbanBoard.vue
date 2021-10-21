<template>
  <div>
    <div>
      <div class="px-2 pb-4" style="background-color: aliceblue; border-bottom: solid 1px #ccc;">
        <button　class="bg-blue-500 border border-blue-500 px-6 py-2 text-white hover:bg-blue-400 rounded"　@click="showModal = true">
          Add Status ＋
        </button>
        <AddStatusModal　v-if="showModal"　@close="showModal = false"　:maxOrderNo="statuses.length"　v-on:status-added="handleStatusAdded"　v-on:status-canceled="closeStatusModal"/>
      </div>
    </div>
    <div class="relative p-2 flex overflow-x-auto h-full">
      <draggable class="flex-1 overflow-hidden" v-model="statuses" v-bind="statusDragOptions" @end="handleStatusMoved">
        <transition-group class="flex-1 flex h-full overflow-x-hidden overflow-y-auto rounded shadow-xs" tag="div">
          <div v-for="status in statuses" :key="status.slug" class="mr-6 w-4/5 max-w-xs flex-1 flex-shrink-0">
            <div class="rounded-md shadow-md overflow-hidden status">
              <div class="p-3 flex justify-between items-baseline bg-blue-800 ">
                <h4 class="font-medium text-white">
                  {{ status.title }}
                </h4>
                <button @click="openAddTaskForm(status.id)" class="pl-40 text-sm text-orange-500 hover:underline">
                  <CreditCardIcon/>
                </button>
                <button @click="delStatus(status.id)" class="p-1 text-sm text-orange-500 hover:underline">
                  <Trash2Icon/>
                </button>
              </div>
              <div class="p-2 bg-blue-100">
                <AddTaskForm v-if="newTaskForStatus === status.id" :status-id="status.id" v-on:task-added="handleTaskAdded" v-on:task-canceled="closeAddTaskForm"/>
                <draggable class="flex-1 overflow-hidden" v-model="status.tasks" v-bind="taskDragOptions" @end="handleTaskMoved">
                  <transition-group class="flex-1 flex flex-col h-full overflow-x-hidden overflow-y-auto rounded shadow-xs" tag="div">
                    <div v-for="task in status.tasks" :key="task.id" class="mb-3 p-3 h-25 flex flex-col bg-white rounded-md shadow transform hover:shadow-md cursor-pointer">
                      <div class="flex justify-between">
                        <span class="block mb-2 text-xl text-gray-900">
                          {{ task.title }}
                        </span>
                        <div>
                          <button aria-label="Delete task" class="p-1 focus:outline-none focus:shadow-outline text-red-500 hover:text-red-600" @click="onDelete(task.id, status.id)">
                            <Trash2Icon/>
                          </button>
                        </div>
                      </div>
                      <p class="text-gray-700 truncate">
                        {{ task.description }}
                      </p>
                      <VueStar animate="animated rubberBand" color="#F05654">
                        <a slot="icon" class="fa fa-heart" @click="handleClick"></a>
                      </VueStar>
                      <ShowCommentModal :taskId="task.id"/>
                    </div>
                  </transition-group>
                </draggable>
                <div v-show="!status.tasks.length && newTaskForStatus !== status.id" class="flex-1 p-4 flex flex-col items-center justify-center">
                  <span class="text-gray-600">タスクがありません</span>
                  <br />
                  <button class="mt-1 text-sm text-orange-600 hover:underline" @click="openAddTaskForm(status.id)">
                    Add Task ＋
                  </button>
                </div>
              </div>
            </div>
          </div>
        </transition-group>
      </draggable>
    </div>
  </div>
</template>

<script>
import AddTaskForm from "./AddTaskForm";
import AddStatusModal from "./AddStatusModal";
import ShowCommentModal from "./ShowCommentModal";
import draggable from "vuedraggable";
import { CreditCardIcon,Trash2Icon } from "vue-feather-icons";
import VueStar from 'vue-star'

export default {
  components: { 
    CreditCardIcon,
    AddTaskForm,
    draggable,
    Trash2Icon,
    AddStatusModal,
    VueStar,
    ShowCommentModal,
  },
  props: {
    initialData: Array
  },
  data() {
    return {
      statuses: [],
      newTaskForStatus: 0,
      showModal: false,
      maxOrderNo: 0,
      handleClick: 0
    };
  },
  computed: {
    taskDragOptions () {
      return {
        animation: 200,
        group: "task-list",
        dragClass: "status-drag"
      };
    },
    statusDragOptions () {
      return {
        animation: 200,
        group: "status-list",
        dragClass: "status-drag"
      };
    }
  },
  mounted() {
    this.statuses = JSON.parse(JSON.stringify(this.initialData));
  },
  methods: {
    openStatusModal() {
      this.showModal = true
    },
    openAddTaskForm(statusId) {
      this.newTaskForStatus = statusId;
    },
    closeStatusModal() {
      this.showModal = false;
    },
    closeAddTaskForm() {
      this.newTaskForStatus = 0;
    },
    handleStatusAdded (newStatus) {
      newStatus.tasks = []
      this.closeStatusModal();
      this.statuses.push(newStatus);
    },
    handleTaskAdded(newTask) {
      const statusIndex = this.statuses.findIndex(
        status => status.id === newTask.status_id
      );
      this.statuses[statusIndex].tasks.push(newTask);
      console.log("add task")
      console.log(this.statuses[statusIndex].tasks)
      this.closeAddTaskForm();
    },
    handleStatusMoved (evt) {
      axios
      .put("/statuses/sync", {columns: this.statuses})
      .then(res => {
        console.log(res.data);
      })
      .catch(err => {
        console.log(err.response);
      });
    },
    handleTaskMoved (evt) {
      axios
      .put("/tasks/sync", {columns: this.statuses})
      .then(res => {
        console.log(res.data);
      })
      .catch(err => {
        console.log(err.response);
      });
    },
    delStatus (statusId) {
      const statusIndex = this.statuses.findIndex(
        status => status.id === statusId
      );

      if (confirm('ステータスを削除しますか？')) {
        axios
        .delete("/statuses/" + statusId, {
            params:{
                statusId: statusId
            }
        })
        .then(res => {
          this.statuses.splice(statusIndex, 1);
        })
        .catch(err => {
          console.log(err);
        });
      }
    },
    onDelete (taskId, statusId) {
      const statusIndex = this.statuses.findIndex(
        status => status.id === statusId
      );
      const taskIndex = this.statuses[statusIndex].tasks.findIndex(
        id => taskId
      );
      console.log("before del task");
      console.log(this.statuses[statusIndex].tasks);
      console.log(taskIndex);
      if (confirm('タスクを削除しますか？')) {
        axios
        .delete("/tasks/" + taskId, {
            params:{
                taskId: taskId
            }
        })
        .then(res => {
          console.log("del task")
          console.log(this.statuses[statusIndex].tasks)
          this.statuses[statusIndex].tasks.splice(taskIndex, 1);
          console.log(this.statuses[statusIndex].tasks)
        })
        .catch(err => {
          console.log(err);
        });
      }
    },
  }
};
</script>

<style scoped>
.status-drag {
  transition: transform 0.5s;
  transition-property: all;
}
.flex {
  display: -webkit-flex;
  display: -moz-flex;
  display: -ms-flex;
  display: -o-flex;
  display: flex;
}
.VueStar {
  position: relative;
  top: -32px;
  left: -34px;
  height: 0px;
}
.VueStar__icon .fa {
  font-size: 1.5em;
  cursor: pointer;
}
</style>
