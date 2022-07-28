import GraphQL from '../GraphQL';

export default (name) => GraphQL.query(`
    mutation {
      forceDestroyContainer (input:{
        name: "${name}"
      }){
        ... on SuccessOutput{
          success
        }
        ... on FailedOutput{
          code
          message
        }
      }
    }`)
    .then((response) => response.json())
    .then((json) => json.data.forceDestroyContainer);
