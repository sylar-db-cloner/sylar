import GraphQL from '../GraphQL';

export default (masterName: string, instanceName: string) =>
  GraphQL.query(
    `
    mutation {
      stopService (input:{
        masterName: "${masterName}"
        instanceName: "${instanceName}"
      }){ 
        ... on SuccessOutput{
          success
        } 
        ... on FailedOutput{
          code
          message
        } 
      } 
    }`,
  )
    .then((response) => response.json())
    .then((json) => json.data.stopService);
