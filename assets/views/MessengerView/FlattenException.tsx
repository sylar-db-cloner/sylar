import { FailedMessageQuery } from '../../gql/graphql';

const FlattenException = ({
  exception,
  message,
}: {
  exception: FailedMessageQuery['failedMessage']['flattenException'];
  message: FailedMessageQuery['failedMessage'];
}) => {
  if (!exception) {
    return null;
  }
  return (
    <ul>
      {exception.message !== message.exceptionMessage && (
        <li>
          Message:
          {exception.message}
        </li>
      )}
      {exception.class && exception.class !== message.className && (
        <li>
          Class:
          {exception.class}
        </li>
      )}
      {exception.headers && (
        <li>
          Headers:
          {exception.headers}
        </li>
      )}
      {exception.file && (
        <li>
          File:
          {exception.file}
        </li>
      )}
      {exception.line && (
        <li>
          Line:
          {exception.line}
        </li>
      )}
      {exception.code !== null && (
        <li>
          Code:
          {exception.code}
        </li>
      )}
      {exception.statusCode && (
        <li>
          StatusCode:
          {exception.statusCode}
        </li>
      )}
      {exception.statusText && (
        <li>
          StatusText:
          {exception.statusText}
        </li>
      )}
      {exception.traceAsString && (
        <li>
          TraceAsString:
          <pre>{exception.traceAsString}</pre>
        </li>
      )}
      {exception && (
        <li>
          Previous:
          <FlattenException exception={exception.previous} message={message} />
        </li>
      )}
    </ul>
  );
};
export default FlattenException;
