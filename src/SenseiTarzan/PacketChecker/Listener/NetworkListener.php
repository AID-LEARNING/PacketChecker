<?php

namespace SenseiTarzan\PacketChecker\Listener;

use pmmp\encoding\BE;
use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\DataDecodeException;
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\Server;
use pocketmine\utils\Limits;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\PacketChecker\Utils\ClientDataToSkinDataHelper;
use SenseiTarzan\PacketChecker\Utils\CommonTypesExtend;
use SenseiTarzan\PacketChecker\Utils\HeaderPacketDecode;
use SenseiTarzan\PacketChecker\Utils\LoginPacketDecode;
use SenseiTarzan\PacketChecker\Utils\TextPacketDecode;
use Throwable;

final class NetworkListener
{

    #[EventAttribute(EventPriority::LOWEST)]
    public function onDataPacketDecode(DataPacketDecodeEvent $event): void {
        $session = $event->getOrigin();
        try {
            switch ($event->getPacketId()){
                case RequestNetworkSettingsPacket::NETWORK_ID:
                {
                    $buffer = new ByteBufferReader($event->getPacketBuffer());
                    HeaderPacketDecode::SkipHeader($buffer, RequestNetworkSettingsPacket::NETWORK_ID);

                    if ($buffer->getUnreadLength() != 4) {
                        throw new PacketHandlingException("Invalid packet length for NetworkSettingsPacket: expected exactly 4 bytes but got " . $buffer->getUnreadLength() . " bytes");
                    }

                    $protocolVersion = BE::readUnsignedInt($buffer);
                    if ($protocolVersion !== ProtocolInfo::CURRENT_PROTOCOL) {
                        throw new PacketHandlingException("Incompatible protocol version: expected " . ProtocolInfo::CURRENT_PROTOCOL . " but got $protocolVersion");
                    }
                    break;
                }
                case LoginPacket::NETWORK_ID: {
                    $buffer = new ByteBufferReader($event->getPacketBuffer());
                    HeaderPacketDecode::SkipHeader($buffer, LoginPacket::NETWORK_ID);

                    if ($buffer->getUnreadLength() < 4) {
                        throw new PacketHandlingException("Not enough data left to read the protocol version, expected at least 4 bytes but only " . $buffer->getUnreadLength() . " bytes remain");
                    }

                    $protocolVersion = BE::readUnsignedInt($buffer);
                    if ($protocolVersion !== ProtocolInfo::CURRENT_PROTOCOL) {
                        throw new PacketHandlingException("Incompatible protocol version: expected " . ProtocolInfo::CURRENT_PROTOCOL . " but got $protocolVersion");
                    }

                    $buffer = new ByteBufferReader(CommonTypesExtend::getString($buffer)); // read the connection request string and create a new buffer for it
                    if($buffer->getUnreadLength() < 4) {
                        throw new PacketHandlingException("Not enough data left to read the chain data length");
                    }
                    $authInfoJsonLength = LE::readUnsignedInt($buffer);
                    if ($buffer->getUnreadLength() < $authInfoJsonLength) {
                        throw new PacketHandlingException("Not enough data left to read the auth info JSON string, expected $authInfoJsonLength bytes but only " . $buffer->getUnreadLength() . " bytes remain");
                    }
                    $buffer->setOffset($buffer->getOffset() + $authInfoJsonLength);

                    if($buffer->getUnreadLength() < 4) {
                        throw new PacketHandlingException("Not enough data left to read the client data JWT length");
                    }
                    $clientDataJwtLength = LE::readUnsignedInt($buffer);
                    if ($buffer->getUnreadLength() < $clientDataJwtLength ||
                        $clientDataJwtLength === 0 ||$clientDataJwtLength === Limits::UINT32_MAX) {
                        throw new PacketHandlingException("Invalid client data JWT length: $clientDataJwtLength bytes");
                    }
                    $clientDataJwt = $buffer->readByteArray($clientDataJwtLength);
                    $clientData = LoginPacketDecode::parseClientData($session, $clientDataJwt);
                    $skinData = ClientDataToSkinDataHelper::safeB64Decode($clientData->SkinData, "SkinData");
                    if($clientData->SkinImageHeight <= 0 || $clientData->SkinImageWidth <= 0) {
                        throw new PacketHandlingException("Skin image dimensions must be positive, got {$clientData->SkinImageWidth}x{$clientData->SkinImageHeight}");
                    }
                    $realLenSkin = $clientData->SkinImageHeight * $clientData->SkinImageWidth * 4;
                    $skinLength = strlen($skinData);
                    if($skinLength == 0 || $skinLength != $realLenSkin ||$skinLength === Limits::UINT32_MAX) {
                        throw new PacketHandlingException("Skin data length $skinLength bytes does not match expected size of $realLenSkin bytes based on the provided dimensions");

                    }
                    $capeData = ClientDataToSkinDataHelper::safeB64Decode($clientData->CapeData, "CapeData");
                    $realLenCape = $clientData->CapeImageHeight * $clientData->CapeImageWidth * 4;
                    $capeLength = strlen($capeData);
                    if($capeLength > 0 && ($capeLength != 8192)) {
                        throw new PacketHandlingException("Cape data length $capeLength bytes does not match expected size of $realLenCape bytes based on the provided dimensions, or is not exactly 8192 bytes");
                    }
                    if(count($clientData->AnimatedImageData) > 256) {
                        throw new PacketHandlingException("Too many skin animations: expected at most 100 but got " . count($clientData->AnimatedImageData));
                    }
                    break;
                }
                case TextPacket::NETWORK_ID: {
                    $buffer = new ByteBufferReader($event->getPacketBuffer());
                    HeaderPacketDecode::SkipHeader($buffer, TextPacket::NETWORK_ID);
                    if ($buffer->getUnreadLength() < 1) {
                        throw new PacketHandlingException("Not enough data left to read the TextPacket type, expected at least 1 byte but only " . $buffer->getUnreadLength() . " bytes remain");
                    }
                    $buffer->setOffset(1); // skip the type byte, we only want to check the needsTranslation flag
                    if($buffer->getUnreadLength() < 1) {
                        throw new PacketHandlingException("Not enough data left to read the TextPacket needsTranslation flag, expected at least 1 byte but only " . $buffer->getUnreadLength() . " bytes remain");
                    }
                    $category = Byte::readUnsigned($buffer);
                    if($category > TextPacketDecode::CATEGORY_MESSAGE_WITH_PARAMETERS) {
                        throw new PacketHandlingException("Invalid TextPacket category: expected a value between 0 and " . TextPacketDecode::CATEGORY_MESSAGE_WITH_PARAMETERS . " but got $category");
                    }
                    $type = Byte::readUnsigned($buffer);
                    switch ($type) {

                        case TextPacket::TYPE_CHAT:
                        case TextPacket::TYPE_WHISPER:
                        case TextPacket::TYPE_ANNOUNCEMENT: {
                            if ($category !== TextPacketDecode::CATEGORY_AUTHORED_MESSAGE) {
                                throw new PacketDecodeException("Decoded TextPacket has invalid structure: type {$type} requires category CATEGORY_AUTHORED_MESSAGE");
                            }
                            CommonTypesExtend::getString($buffer, 32); // check source name length
                            CommonTypesExtend::getString($buffer, 512); // check message length
                            break;
                        }

                        case TextPacket::TYPE_RAW:
                        case TextPacket::TYPE_TIP:
                        case TextPacket::TYPE_SYSTEM:
                        case TextPacket::TYPE_JSON_WHISPER:
                        case TextPacket::TYPE_JSON:
                        case TextPacket::TYPE_JSON_ANNOUNCEMENT: {
                            if ($category !== TextPacketDecode::CATEGORY_MESSAGE_ONLY) {
                                throw new PacketDecodeException("Decoded TextPacket has invalid structure: type {$type} requires category CATEGORY_MESSAGE_ONLY");
                            }
                            CommonTypesExtend::getString($buffer, 512); // check message length
                            break;
                        }
                        case TextPacket::TYPE_TRANSLATION:
                        case TextPacket::TYPE_POPUP:
                        case TextPacket::TYPE_JUKEBOX_POPUP: {
                            if ($category !== TextPacketDecode::CATEGORY_MESSAGE_WITH_PARAMETERS) {
                                throw new PacketDecodeException("Decoded TextPacket has invalid structure: type {$type} requires category CATEGORY_MESSAGE_WITH_PARAMETERS");
                            }
                            CommonTypesExtend::getString($buffer, 512); // check message length
                            $count = VarInt::readUnsignedInt($buffer);
                            if($count > 1000) {
                                throw new PacketHandlingException("Too many parameters in TextPacket: expected at most 1000 but got $count");
                            }
                            for ($i = 0; $i < $count; ++$i) {
                                CommonTypesExtend::getString($buffer, 512); // check parameter length
                            }
                            break;
                        }
                        default:
                            throw new DataDecodeException("Invalid TextPacket type: expected a value between 0 and " . TextPacket::TYPE_JSON_ANNOUNCEMENT . " but got $type");
                    }
                }
                case PlayerSkinPacket::NETWORK_ID: {
                    $buffer = new ByteBufferReader($event->getPacketBuffer());
                    HeaderPacketDecode::SkipHeader($buffer, PlayerSkinPacket::NETWORK_ID);
                    if ($buffer->getUnreadLength() < 16) {
                        throw new PacketHandlingException("Not enough data left to read the UUID, expected at least 16 bytes but only " . $buffer->getUnreadLength() . " bytes remain");
                    }
                    $buffer->setOffset($buffer->getOffset() + 16); // skip the UUID, we only want to check the skin data
                    CommonTypesExtend::checkSkin($buffer); // this will perform all necessary checks on the skin data
                    CommonTypesExtend::getString($buffer, 64); // check new skin name length
                    CommonTypesExtend::getString($buffer, 64); // check old skin name length
                     break;
                }
                default:
                    break;
            }
        }catch (PacketHandlingException $e) {
            Server::getInstance()->getLogger()->debug("PacketHandlingException while handling packet " . $event->getPacketId() . ": " . $e->getMessage());
            $event->cancel();
            $session->disconnect(KnownTranslationFactory::pocketmine_disconnect_error_badPacket());
        }catch (DataDecodeException $e) {
            Server::getInstance()->getLogger()->debug("DataDecodeException while decoding packet " . $event->getPacketId() . ": " . $e->getMessage());
            $event->cancel();
        }catch (Throwable){
            return; // catch any other unexpected exceptions to avoid crashing the server, but don't cancel the event since we don't know if the packet is actually malformed or if there was just an error in our code
        }
    }
}