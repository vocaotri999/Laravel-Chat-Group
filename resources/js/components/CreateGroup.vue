<template>
    <div class="panel panel-default">
        <div class="panel-heading">Create Group</div>
        <div class="panel-body">
            <form>
                <div class="form-group">
                    <input class="form-control" type="text" v-model="name" placeholder="Group Name">
                </div>
                <div class="form-group">
                    <select multiple id="friends" class="form-control">
                        <option v-for="user in users" :value="user.id" :class="user.status|formatStatus">
                            {{ user.name }}             online : {{user.last_online|formatFromNow}}
                        </option>
                    </select>
                </div>
            </form>
        </div>
        <div class="panel-footer text-center">
            <button type="submit" @click.prevent="createGroup" class="btn btn-primary">Create Group</button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['initialUsers'],
    data() {
        return {
            name: '',
            users: []
        }
    },
    mounted() {
        this.listenForUserOnline();
    },
    created() {
        this.loadOtherUser();
    },
    filters: {
        formatStatus: function (value) {
            if (value === 0) return "red";
            return "green";
        },
        formatFromNow: function (value){
            if (!value) return '';
            return moment(value).lang("vi").fromNow()
        }
    },
    methods: {
        loadOtherUser() {
            axios.get('/userOther')
                .then((user) => {
                    this.users = user.data;
                });
        },
        createGroup() {
            axios.post('/groups', {name: this.name, users: this.users})
                .then((response) => {
                    this.name = '';
                    this.users = [];
                    Bus.$emit('groupCreated', response.data);
                });
        },
        listenForUserOnline() {
            Echo.join('chat')
                .listen('UserOnline', (e) => {
                    this.loadOtherUser();
                })
                .listen('UserOffline', (e) => {
                    this.loadOtherUser();
                })
                .joining((user) => {
                    axios.post('/userOnline/' + user.id);
                    this.loadOtherUser();
                })
                .leaving((user) => {
                    axios.post('/userOffline/' + user.id);
                    this.loadOtherUser();
                })
        }
    }
}
</script>

<style scoped>
#friends option:after {
    content: "";
    height: 7px;
    width: 7px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    top: 9px;
    float: right;
}

#friends option.red:after {
    background: #c00;
}

#friends option.green:after {
    background: #0c0;
}

#friends option.blue:after {
    background: #00c;
}
</style>
