<template>
    <fj-container>
        <fj-header :title="'Roles'"></fj-header>
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col" v-for="role in roles">
                                {{ role.name.capitalize() }}
                            </th>
                        </tr>
                    </thead>
                    <tr v-for="user in users">
                        <td scope="row">
                            <strong>{{ user.name }}</strong> ({{ user.email }})
                        </td>
                        <td v-for="role in roles">
                            <b-form-checkbox
                                v-model="data[user.name][role.name]"
                                @change="toggleRole(role, user)"
                                name="check-button"
                                switch
                            >
                            </b-form-checkbox>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </fj-container>
</template>

<script>
export default {
    name: 'UserRoles',
    props: {
        roles: {
            type: Array
        },
        users: {
            type: Array
        },
        user_roles: {
            type: Array
        }
    },
    data() {
        return {
            data: {},
            test: null
        };
    },
    beforeMount() {
        for (var i = 0; i < this.users.length; i++) {
            let user = this.users[i];
            this.$set(this.data, user.name, {});
            for (var p = 0; p < this.roles.length; p++) {
                let role = this.roles[p];
                this.$set(
                    this.data[user.name],
                    role.name,
                    this.userHasRole(role, user)
                );
            }
        }
    },
    methods: {
        userHasRole(role, user) {
            return (
                _.size(
                    _.filter(this.user_roles, {
                        role_id: role.id,
                        model_id: user.id
                    })
                ) > 0
            );
        },
        async toggleRole(role, user) {
            let payload = {
                role,
                user
            };
            let respose = await axios.put('/user_roles', payload);

            let current_user = JSON.parse(JSON.stringify(this.data[user.name]));

            for (var current_role in current_user) {
                if (current_user.hasOwnProperty(current_role)) {
                    if (current_role != role.name) {
                        this.$set(this.data[user.name], current_role, false);
                    }
                }
            }
        }
    }
};
</script>
