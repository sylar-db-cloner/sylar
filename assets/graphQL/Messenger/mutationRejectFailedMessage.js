import GraphQL from "../GraphQL";

export default (ids) => GraphQL.mutation( `
mutation {
    rejectFailedMessage(input:{
        ids: ${JSON.stringify(ids)}
    }) {
    success
    }
}`)
    .then((response) => response.json())
    .then((responseAsJson) => responseAsJson.data.rejectFailedMessage);